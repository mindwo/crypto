/**
 * Crypto library which encrypt and decrypt data
 * @returns {window.DxCryptoClass}
 */
window.DxCryptoClass = function () {
    this.certificate;
    this.masterKeyGroups = new Array();
    this.userId;
};

/**
 * Extends crypto library prototype
 * @param {object} param1 Crypto library
 * @param {function} param2 Extended functionality
 */
$.extend(window.DxCryptoClass.prototype, {
    /**
     * Catches error and output error to user
     * @param {object} err Error details
     * @param {type} msg Error message (optional)
     * @returns {undefined}
     */
    catchError: function (err, msg) {
        hide_page_splash(1);

        if (err && err.exit) {
            throw {exit: true};
        }

        if (!msg) {
            msg = Lang.get('crypto.e_unknown');
        }

        if (err && err.e_custom) {
            msg = err.msg;
        }

        notify_err(msg);

        throw {exit: true};
    },
    /**
     * Clears crypto cache
     * @returns {undefined}
     */
    clearCryptoCache: function () {
        window.DxCrypto.certificate = undefined;
        window.DxCrypto.userId = undefined;
        window.DxCrypto.rawCertificate = undefined;
        window.DxCrypto.rawMasterKeys = new Array();
        window.DxCrypto.masterKeyGroups = new Array();
    },
    /**
     * Create passwords CryptoKey object from given password
     * @param {string} password
     * @returns {undefined}
     */
    createPasswordKey: function (password) {
        var self = window.DxCrypto;

        // don't use native approaches for converting text, otherwise international
        // characters won't have the correct byte sequences. Use TextEncoder when
        // available or otherwise use relevant polyfills
        var passwordBuffer = self.stringToArrayBuffer(password);

        // Import password to CryptoKey object - base key which contains plain password castet as CryptoKey object
        return window.crypto.subtle.importKey(
                'raw',
                passwordBuffer,
                {name: 'PBKDF2'},
                false,
                ['deriveKey']
                )
                .then(function (baseKey) {
                    return self.derivePasswordKey(baseKey);
                })
                .catch(window.DxCrypto.catchError);
    },
    /**
     * Derives password CryptoKey from base CryptoKey object
     * @param {CryptoKey} baseKey Key imported from plain string
     * @returns {undefined}
     */
    derivePasswordKey: function (baseKey) {
        // salt should be Uint8Array or ArrayBuffer
        var saltBuffer = window.DxCrypto.stringToArrayBuffer('YyZYm6EuGaa1BhbDPwjy');

        return window.crypto.subtle.deriveKey(
                {"name": 'PBKDF2',
                    "salt": saltBuffer,
                    // don't get too ambitious, or at least remember
                    // that low-power phones will access your app
                    "iterations": 1000,
                    "hash": 'SHA-256'
                },
                baseKey,
                {"name": 'AES-CTR', "length": 256}, // For AES the length required to be 128 or 256 bits (not bytes)

                false, // Whether or not the key is extractable (less secure) or not (more secure) when false, the key can only be passed as a web crypto object, not 

                ["wrapKey", "unwrapKey"] // this web crypto object will only be allowed for these functions
                )
                .catch(window.DxCrypto.catchError);
    },
    /**
     * Generates users certificate (with public and private keys)
     * @param {CryptoKey} passwordKey Password 
     * @returns {undefined}
     */
    generateUserCert: function (passwordKey) {
        window.crypto.subtle.generateKey(
                {
                    name: "RSA-OAEP",
                    modulusLength: 2048, //can be 1024, 2048, or 4096
                    publicExponent: new Uint8Array([0x01, 0x00, 0x01]),
                    hash: {name: "SHA-256"} //can be "SHA-1", "SHA-256", "SHA-384", or "SHA-512"
                },
                true, //whether the key is extractable (i.e. can be used in exportKey)
                ["wrapKey", "unwrapKey"] //must be ["encrypt", "decrypt"] or ["wrapKey", "unwrapKey"]
                )
                .then(function (asyncKey) {
                    window.DxCrypto.wrapPrivateKey(passwordKey, asyncKey);
                })
                .catch(window.DxCrypto.catchError);
    },
    /**
     * Wraps users private key with passwords key
     * @param {CryptoKey} passwordKey Password key
     * @param {CryptoKey} asyncKey Users certificate
     * @returns {undefined}
     */
    wrapPrivateKey: function (passwordKey, asyncKey) {
        window.DxCrypto.certificate = asyncKey;

        window.crypto.subtle.wrapKey(
                "pkcs8", //can be "jwk", "raw", "spki", or "pkcs8"
                asyncKey.privateKey, // CTR the key you want to wrap, must be able to export to "raw" format // CBC the key you want to wrap, must be able to export to above format
                passwordKey, //the AES-CTR key with "wrapKey" usage flag
                {//these are the wrapping key's algorithm options
                    name: "AES-CTR",
                    //Don't re-use counters!
                    //Always use a new counter every time your encrypt!
                    counter: new Uint8Array(16),
                    length: 128 //can be 1-128
                })
                .then(function (wrappedPrivateKey) {
                    window.crypto.subtle.exportKey(
                            "spki", //can be "jwk" (public or private), "spki" (public only), or "pkcs8" (private only)
                            asyncKey.publicKey //can be a publicKey or privateKey, as long as extractable was true
                            )
                            .then(function (publicKeyBuffer) {
                                window.DxCrypto.saveUserCert(publicKeyBuffer, wrappedPrivateKey);
                            })
                            .catch(window.DxCrypto.catchError);
                })
                .catch(window.DxCrypto.catchError);
    },
    /**
     * Saves users certificate
     * @param {array} publicKeyBuffer Public key's array buffer
     * @param {array} wrappedPrivateKey Wrapped private key's array buffer
     * @returns {undefined}
     */
    saveUserCert: function (publicKeyBuffer, wrappedPrivateKey) {
        var self = window.DxCrypto;

        var publicKeyBlob = new Blob([new Uint8Array(publicKeyBuffer)], {type: "application/octet-stream"});
        var privateKeyBlob = new Blob([new Uint8Array(wrappedPrivateKey)], {type: "application/octet-stream"});

        var container = new FormData();
        container.append('public_key', publicKeyBlob);
        container.append('private_key', privateKeyBlob);

        $.ajax({
            url: DX_CORE.site_url + 'crypto/save_cert',
            data: container,
            type: "post",
            processData: false,
            dataType: "json",
            contentType: false,
            success: function (res) {
                hide_page_splash(1);

                if (res && res.success) {
                    notify_info(Lang.get('crypto.i_save_cert_success'));
                    $('.dx-crypto-generate-cert-btn').hide();
                    $('.dx-crypto-generate-new-cert-btn').show();
                    $('.dx-crypto-generate-cert-info').hide();
                    $('.dx-crypto-generate-new-cert-info').show();
                } else {
                    self.catchError(res);
                }
            },
            error: function (err) {
                self.catchError(err, Lang.get('crypto.e_save'));
            }
        });
    },
    /**
     * Converts string to array buffer
     * @param {string} string Inpur string
     * @returns {array} String converted to array buffer
     */
    stringToArrayBuffer: function (string) {
        var encoder = new TextEncoder("utf-8");
        return encoder.encode(string);
    },
    /**
     * Converts array buffer to string
     * @param {ArrayBuffer} arrayBuffer Buffer which must be converted
     * @returns {string} Array Buffer converted to string
     */
    arrayBufferToString: function (arrayBuffer) {
        var decoder = new TextDecoder("utf-8");

        return decoder.decode(new Uint8Array(arrayBuffer));
    },
    /**
     * Converts array buffer to Hex string
     * @param {ArrayBuffer} arrayBuffer Array buffer which will be converted to Hex string
     * @returns {string} Output Hex string
     */
    arrayBufferToHexString: function (arrayBuffer) {
        var byteArray = new Uint8Array(arrayBuffer);
        var hexString = "";
        var nextHexByte;

        for (var i = 0; i < byteArray.byteLength; i++) {
            nextHexByte = byteArray[i].toString(16);
            if (nextHexByte.length < 2) {
                nextHexByte = "0" + nextHexByte;
            }
            hexString += nextHexByte;
        }
        return hexString;
    },
    /**
     * Converts Hex string to Array Buffer
     * @param {string} hex Hexstring to convert
     * @returns {Uint8Array} Output buffer
     */
    hexStringToArrayBuffer: function (hex) {
        var view = new Uint8Array(hex.length / 2)

        for (var i = 0; i < hex.length; i += 2) {
            view[i / 2] = parseInt(hex.substring(i, i + 2), 16)
        }

        return view;
    },
    /**
     * Convert base64 string to array buffer
     * @param {string} base64 Base 64 string to convert
     * @returns {ArrayBuffer} Output buffer
     */
    base64ToArrayBuffer: function (base64) {
        var binary_string = window.atob(base64);
        var len = binary_string.length;
        var bytes = new Uint8Array(len);
        for (var i = 0; i < len; i++) {
            bytes[i] = binary_string.charCodeAt(i);
        }
        return bytes.buffer;
    },
    /**
     * Encryptes fields
     * @param {DOM} cryptoFields Jquery crypto objects to encrypt
     * @param {obj} event Current event object
     * @param {function} callback Function which will be called after data is encrypted
     * @returns {Boolean} If operation succedded
     */
    encryptFields: function (cryptoFields, event, callback) {
        var cryptoFieldCount = cryptoFields.length;
        var cryptoFieldCounter = 0;

        var onFinishing = function () {
            // Modifies original event data by setting that encryption is finished
            event.encryptionFinished = true;

            // Calls forms save button
            callback(event);
        };

        if (cryptoFieldCount <= 0) {
            onFinishing();
            return true;
        }

        show_page_splash(1);

        var self = window.DxCrypto;

        // Check if crypto file field has been changed then request certificate check 
        var hasChangedFileFields = self.checkFileCryptoFields(cryptoFields);

        // If user have changed crypto file then must have certificate
        if (hasChangedFileFields && (!self.certificate || !self.certificate.publicKey)) {
            // Retrieves certificate and calls this function again
            self.getCurrentUserCertificate(0, function () {
                self.encryptFields(cryptoFields, event, callback);
            });
            return false;
        }

        if (!self.certificate || !self.certificate.publicKey) {
            onFinishing();

            return false;
        }

        cryptoFields.each(function () {
            var cryptoField = this;

            var masterKeyGroupId = $(cryptoField).data('masterkey-group');

            if ($(cryptoField).hasClass('dx-crypto-field-file') && $(cryptoField).is('input') && cryptoField.files.length === 0) {
                if (cryptoFieldCount === ++cryptoFieldCounter) {
                    onFinishing();
                }

                return true;
            }

            if ($(cryptoField).hasClass('dx-crypto-field') && $(cryptoField).data('is-decrypted') != 1) {
                if (cryptoFieldCount === ++cryptoFieldCounter) {
                    onFinishing();
                }

                return true;
            }

            // Key is not found for user
            if (!(masterKeyGroupId in self.masterKeyGroups)) {
                cryptoField.crypto.setAccessError();

                if (cryptoFieldCount === ++cryptoFieldCounter) {
                    onFinishing();
                }

                return true;
            }

            var onReceiveValue = function (decryptedData) {
                var counterBuffer = new Uint8Array(16);

                window.crypto.subtle.encrypt(
                        {
                            name: "AES-CTR",
                            //Don't re-use counters!
                            //Always use a new counter every time your encrypt!
                            counter: counterBuffer,
                            length: 128, //can be 1-128
                        },
                        self.masterKeyGroups[masterKeyGroupId], //from generateKey or importKey above
                        decryptedData //ArrayBuffer of the data
                        )
                        .then(function (encryptedValue) {
                            encryptedValue = new Uint8Array(encryptedValue);

                            var resBuffer = new Uint8Array(encryptedValue.length + counterBuffer.length);
                            resBuffer.set(counterBuffer);
                            resBuffer.set(encryptedValue, counterBuffer.length);

                            if ($(cryptoField).hasClass('dx-crypto-field-file')) {
                                cryptoField.crypto.setValue(resBuffer);
                            } else {
                                cryptoField.crypto.setValue(resBuffer, false);
                            }

                            $(cryptoField).data('is-decrypted', 0);

                            // If end move to next field
                            if (cryptoFieldCount === ++cryptoFieldCounter) {
                                onFinishing();

                                return true;
                            }
                        })
                        .catch(window.DxCrypto.catchError);
            };

            cryptoField.crypto.getValue(onReceiveValue);
        });
    },
    /**
     * Check if crypto file field has been changed then request certificate check
     * @param {DOM} cryptoFields Fields which are encrypted
     * @returns {Boolean} If true then must have valid certificate
     */
    checkFileCryptoFields: function (cryptoFields) {
        var res = false;

        cryptoFields.each(function () {
            var cryptoField = this;

            if ($(cryptoField).hasClass('dx-crypto-field-file') && $(cryptoField).is('input') && cryptoField.files.length > 0) {
                res = true;
                return;
            }
        });

        return res;
    },
    /**
     * Decryptes all fields
     * @param {DOM} cryptoFields Jquery crypto objects to encrypt
     * @returns {Boolean} If operation succedded
     */
    decryptFields: function (cryptoFields) {
        var cryptoFieldCount = cryptoFields.length;
        var cryptoFieldCounter = 0;

        if (cryptoFieldCount <= 0) {
            return true;
        }

        show_page_splash(1);

        var self = window.DxCrypto;

        if (!self.certificate || !self.certificate.privateKey) {
            // Retrieves certificate and calls this function again
            self.getCurrentUserCertificate(0, function () {
                self.decryptFields(cryptoFields);
            });
            return false;
        }

        // Async recursive action...
        cryptoFields.each(function () {
            var cryptoField = this;

            var masterKeyGroupId = $(cryptoField).data('masterkey-group');

            if ($(cryptoField).hasClass('dx-crypto-field') && $(cryptoField).data('is-decrypted') == 1) {
                if (cryptoFieldCount == ++cryptoFieldCounter) {
                    hide_page_splash(1);
                }
                return true;
            }

            // Key is not found for user
            if (!(masterKeyGroupId in self.masterKeyGroups)) {
                if (cryptoFieldCount === ++cryptoFieldCounter) {
                    hide_page_splash(1);
                }

                cryptoField.crypto.setAccessError();
                return true;
            }

            var onReceiveValue = function (encryptedData, fileType) {
                var setDecryptedValue = function (resBuffer) {
                    if ($(cryptoField).hasClass('dx-crypto-field-file')) {
                        cryptoField.crypto.setValue(resBuffer, fileType);
                    } else {
                        cryptoField.crypto.setValue(resBuffer, true);
                    }

                    $(cryptoField).data('is-decrypted', 1);

                    cryptoFieldCounter++;

                    // If end move to next field
                    if (cryptoFieldCount === cryptoFieldCounter) {
                      hide_page_splash(1);
                        return true;
                    }
                };

                if (encryptedData == '') {
                    setDecryptedValue('');
                    return true;
                }

                var counterBuffer = encryptedData.subarray(0, 16);

                var resBuffer = encryptedData.subarray(16, encryptedData.length);

                window.crypto.subtle.decrypt(
                        {
                            name: "AES-CTR",
                            counter: counterBuffer, //The same counter you used to encrypt
                            length: 128, //The same length you used to encrypt
                        },
                        self.masterKeyGroups[masterKeyGroupId], //from generateKey or importKey above
                        resBuffer //ArrayBuffer of the data
                        )
                        .then(function (decryptedValue) {
                            setDecryptedValue(decryptedValue);
                        })
                        .catch(window.DxCrypto.catchError);
            };

            cryptoField.crypto.getValue(onReceiveValue);
        });
    },
    /**
     * Open modal window and ask user to input his certificate's password
     * @param {function} callback Function to call after certificate has been retrieved
     * @returns {undefined}
     */
    requestUserCertificatePassword: function (callback) {
        hide_page_splash(1);

        var modal = $('#dx-crypto-modal-psw');

        var accept_btn = $('.dx-crypto-modal-accept', modal);

        accept_btn.off('click');

        accept_btn.click(function () {
            show_page_splash(1);

            var res = window.DxCrypto.decryptUserCertificate(callback);

            if (res || typeof (res) == 'undefined') {
                modal.modal('hide');
            }
        });

        $('#dx-crypto-modal-input-password', modal).off('keypress');
        $('#dx-crypto-modal-input-password', modal).keypress(function (e) {
            if (e.keyCode == 13)
                accept_btn[0].click();
        });

        modal.on('shown.bs.modal', function () {
            $('#dx-crypto-modal-input-password', modal).focus();
        });

        modal.modal('show');
    },
    /**
     * Compares array buffers if they are equal
     * @param {ArrayBuffer} buf1
     * @param {ArrayBuffer} buf2
     * @returns {Boolean} Result if buffer are equal
     */
    compareArrayBuffers: function (buf1, buf2)
    {
        if (buf1.byteLength != buf2.byteLength)
            return false;
        var dv1 = new Int8Array(buf1);
        var dv2 = new Int8Array(buf2);
        for (var i = 0; i != buf1.byteLength; i++)
        {
            if (dv1[i] != dv2[i])
                return false;
        }
        return true;
    },
    /**
     * Decryptes user's certificate and stores in memory
     * @param {function} callback Function to call after certificate has been retrieved
     * @returns {undefined}
     */
    decryptUserCertificate: function (callback) {
        show_page_splash(1);

        var password = $('#dx-crypto-modal-input-password').val();
        $('#dx-crypto-modal-input-password').val('');

        var self = window.DxCrypto;

        // Import public key from raw format to CryptoKey object
        window.crypto.subtle.importKey(
                "spki", //can be "jwk" (public or private), "spki" (public only), or "pkcs8" (private only)
                self.rawCertificate.publicKey,
                {//these are the algorithm options
                    name: "RSA-OAEP",
                    hash: {name: "SHA-256"}, //can be "SHA-1", "SHA-256", "SHA-384", or "SHA-512"
                },
                false, //whether the key is extractable (i.e. can be used in exportKey)
                ["wrapKey"] //"encrypt" or "wrapKey" for public key import or
                //"decrypt" or "unwrapKey" for private key imports
                )
                .then(function (publicKey) {
                    // Saves imported public key
                    window.DxCrypto.certificate = {
                        publicKey: publicKey
                    };

                    // Generate password key (Cryptokey object) from password
                    return window.DxCrypto.createPasswordKey(password);
                })
                .then(function (passwordKey) {
                    // Unwraps password
                    return self.unwrapPrivateKey(passwordKey, self.rawCertificate.privateKey);
                })
                .then(function (privateKey) {
                    self.certificate.privateKey = privateKey;

                    self.rawCertificate = undefined;

                    return self.unwrapMasterKey();
                })
                .then(function () {
                    callback();
                })
                .catch(self.catchError);
    },
    /**
     * Unwraps user's private key
     * @param {CryptoKey} passwordKey Password CryptoKey object derived from password
     * @param {ArrayBuffer} wrappedPrivateKey ArrayBuffer contains private key
     * @returns {CryptoKey} Unwrapped private key
     */
    unwrapPrivateKey: function (passwordKey, wrappedPrivateKey) {
        return window.crypto.subtle.unwrapKey(
                "pkcs8", //"jwk", "raw", "spki", or "pkcs8" (whatever was used in wrapping)
                wrappedPrivateKey, //the key you want to unwrap
                passwordKey, //the AES-CTR key with "unwrapKey" usage flag
                {//these are the wrapping key's algorithm options
                    name: "AES-CTR",
                    //Don't re-use counters!
                    //Always use a new counter every time your encrypt!
                    counter: new Uint8Array(16),
                    length: 128 //can be 1-128
                },
                {//this what you want the wrapped key to become (same as when wrapping)
                    name: "RSA-OAEP",
                    modulusLength: 2048, //can be 1024, 2048, or 4096
                    publicExponent: new Uint8Array([0x01, 0x00, 0x01]),
                    hash: {name: "SHA-256"} //can be "SHA-1", "SHA-256", "SHA-384", or "SHA-512"
                },
                false, //whether the key is extractable (i.e. can be used in exportKey)
                ["unwrapKey"] //the usages you want the unwrapped key to have
                )
                .then(function (privateKey) {
                    return privateKey;
                })
                .catch(function (err) {
                    window.DxCrypto.clearCryptoCache();

                    window.DxCrypto.catchError(err, Lang.get('crypto.e_password_incorrect'));
                });
    },
    /**
     * Unwraps master key (recursive - after unwraping one key, proceedes to next one)
     * @param {type} counter Counts current master key which i being unwrapped
     * @returns {Boolean} Result if operation succeeded
     */
    unwrapMasterKey: function (counter) {
        var self = window.DxCrypto;

        if (counter == undefined) {
            counter = 0;
        }

        if (!self.rawMasterKeys || self.rawMasterKeys == undefined || (self.rawMasterKeys && !(counter < self.rawMasterKeys.length && 0 < self.rawMasterKeys.length))) {
            self.rawMasterKeys = new Array();
            return false;
        }

        var masterKeyObj = self.rawMasterKeys[counter];

        return   window.crypto.subtle.unwrapKey(
                "raw", //"jwk", "raw", "spki", or "pkcs8" (whatever was used in wrapping)
                masterKeyObj.value, //the key you want to unwrap
                self.certificate.privateKey, //the AES-CTR key with "unwrapKey" usage flag
                {//these are the wrapping key's algorithm options
                    name: "RSA-OAEP",
                    modulusLength: 2048, //can be 1024, 2048, or 4096
                    publicExponent: new Uint8Array([0x01, 0x00, 0x01]),
                    hash: {name: "SHA-256"} //can be "SHA-1", "SHA-256", "SHA-384", or "SHA-512"
                },
                {
                    name: "AES-CTR",
                    length: 256
                },
                true, //whether the key is extractable (i.e. can be used in exportKey)
                ["encrypt", "decrypt"] //the usages you want the unwrapped key to have
                )
                .then(function (masterKey) {
                    self.masterKeyGroups[masterKeyObj.id] = masterKey;

                    // Iterate to next master key
                    return self.unwrapMasterKey(++counter);
                })
                .catch(window.DxCrypto.catchError);
    },
    /**
     * Get specified users certificate
     * @param {int} userId User's ID whose certificate we want to retrieve
     * @param {int} masterKeyGroupId Master key's group which will be retrieved while getting certificate. Can be 0 tu retrieve all keys
     * @param {function} callback Function to call after certificate has been retrieved
     * @returns {undefined}
     */
    getUserCertificate: function (userId, masterKeyGroupId, callback) {
        var self = window.DxCrypto;

        $.ajax({
            url: DX_CORE.site_url + 'crypto/get_user_cert/' + userId + '/' + masterKeyGroupId,
            type: "get",
            success: function (res) {
                if (res && res.success && res.success == 1) {
                    if (!userId || userId <= 0) {
                        self.userId = res.user_id;
                    }

                    var public_key = new Uint8Array(self.base64ToArrayBuffer(res.public_key));
                    var private_key = new Uint8Array(self.base64ToArrayBuffer(res.private_key));

                    var master_keys = new Array();
                    if (res.master_keys) {
                        for (var i = 0; i < res.master_keys.length; i++) {
                            res.master_keys[i].value = self.hexStringToArrayBuffer(res.master_keys[i].value);
                        }

                        master_keys = res.master_keys;
                    }

                    callback(public_key, private_key, master_keys);
                } else {
                    if (res.msg) {
                        self.catchError(res, res.msg);
                    } else {
                        self.catchError(res, Lang.get('crypto.e_get_user_cert'));
                    }
                }
            },
            error: function (err) {
                self.catchError(err, Lang.get('crypto.e_get_user_cert'));
            }
        });
    },
    /**
     * Gets current users certificate from server and the store it in memory.
     * @param {int} masterKeyGroupId Master key's group which will be retrieved while getting certificate. Can be 0 tu retrieve all keys
     * @param {function} callback Function to call after certificate has been stored in memory
     * @returns {undefined}
     */
    getCurrentUserCertificate: function (masterKeyGroupId, callback) {
        var self = window.DxCrypto;

        self.getUserCertificate(0, masterKeyGroupId, function (public_key, private_key, master_keys) {
            if (master_keys) {
                self.rawMasterKeys = master_keys;
            } else {
                self.rawMasterKeys = new Array();
            }

            self.rawCertificate = {
                publicKey: public_key,
                privateKey: private_key
            };

            self.requestUserCertificatePassword(callback);
        });
    },
    /**
     * Generates completely new master key
     * @param {CryptoKey} publicKey Key which will be used to wrap master key
     * @param {int} masterKeyGroupId Master key group ID
     * @param {function} callback Function to call after master key has been generated
     * @returns {ArrayBuffer} Master key which is generated
     */
    generateNewMasterKey: function (publicKey, masterKeyGroupId, callback) {
        return window.crypto.subtle.generateKey(
                {
                    name: "AES-CTR",
                    length: 256 //can be  128, 192, or 256
                },
                true, //whether the key is extractable (i.e. can be used in exportKey)
                ["encrypt", "decrypt"] //must be ["encrypt", "decrypt"] or ["wrapKey", "unwrapKey"]
                )
                .then(function (masterKey) {
                    window.DxCrypto.masterKeyGroups[masterKeyGroupId] = masterKey;

                    return window.crypto.subtle.wrapKey(
                            "raw", //the export format, must be "raw" (only available sometimes)
                            masterKey, //the key you want to wrap, must be able to fit in RSA-OAEP padding
                            publicKey, //the public key with "wrapKey" usage flag
                            {//these are the wrapping key's algorithm options
                                name: "RSA-OAEP",
                                hash: {name: "SHA-256"}
                            });
                })
                .then(function (wrappedMasterKey) {
                    callback(wrappedMasterKey);
                })
                .catch(window.DxCrypto.catchError);
    },
    /**
     * Generates master key for user in specified master key group
     * @param {int} masterKeygroupId Master keys group ID
     * @param {int} userId User's ID
     * @param {function} callback Function to call after master key has been created
     * @returns {undefined}
     */
    generateMasterKey: function (masterKeyGroupId, userId, callback) {
        var self = window.DxCrypto;

        var selfCall = function () {
            self.generateMasterKey(masterKeyGroupId, userId, callback);
        };

        if (!self.certificate || !self.certificate.publicKey) {
            // Retrieves certificate and calls this function again
            self.getCurrentUserCertificate(0, selfCall);
            return false;
        }

        if (!(masterKeyGroupId in self.masterKeyGroups)) {
            if (self.userId == userId) {
                $.ajax({
                    url: DX_CORE.site_url + 'crypto/check_existing_keys/' + masterKeyGroupId,
                    type: "get",
                    dataType: "json",
                    success: function (res) {
                        hide_page_splash(1);

                        if (res && res.has_keys && res.has_keys == 1) {
                            self.catchError(null, Lang.get('crypto.e_master_key_already_exist'));
                        } else {
                            // Generates master key for current user
                            self.generateNewMasterKey(window.DxCrypto.certificate.publicKey, masterKeyGroupId, callback);
                        }
                    },
                    error: function (err) {
                        self.catchError(err, Lang.get('crypto.e_master_key_already_exist'));
                    }
                });
            } else {
                self.catchError(null, Lang.get('crypto.e_add_yourself_first'));
            }
        } else {
            // Try to get certificate for specified user
            self.getUserCertificate(userId, masterKeyGroupId, function (raw_public_key) {
                window.crypto.subtle.importKey(
                        "spki", //can be "jwk" (public or private), "spki" (public only), or "pkcs8" (private only)
                        raw_public_key,
                        {//these are the algorithm options
                            name: "RSA-OAEP",
                            hash: {name: "SHA-256"}, //can be "SHA-1", "SHA-256", "SHA-384", or "SHA-512"
                        },
                        false, //whether the key is extractable (i.e. can be used in exportKey)
                        ["wrapKey"] //"encrypt" or "wrapKey" for public key import or
                        //"decrypt" or "unwrapKey" for private key imports
                        )
                        .then(function (public_key) {
                            // Wraps current user's master key with other user's public key 
                            return window.crypto.subtle.wrapKey(
                                    "raw", //the export format, must be "raw" (only available sometimes)
                                    window.DxCrypto.masterKeyGroups[masterKeyGroupId], //the key you want to wrap, must be able to fit in RSA-OAEP padding
                                    public_key, //the public key with "wrapKey" usage flag
                                    {//these are the wrapping key's algorithm options
                                        name: "RSA-OAEP",
                                        hash: {name: "SHA-256"}
                                    });
                        })
                        .then(function (wrappedMasterKey) {
                            callback(wrappedMasterKey);
                        })
                        .catch(window.DxCrypto.catchError);
            });
        }
    },
    /**
     * Event on master key save button click - calls function which generates key derived from existing one or creates a new one
     * @param {type} event
     * @param {type} form
     * @returns {Boolean}
     */
    onMasterKeysSave: function (event, form) {
        var btnSave = $('.dx-btn-save-form', form);

        var isMasterKeyGenerated = btnSave.data('is-masterkey-generated');

        if (isMasterKeyGenerated == 1) {
            return true;
        }

        show_page_splash(1);

        event.stopImmediatePropagation();

        var userId = $('input[name=user_id]', form).val();

        var masterKeyGroupId = $('input[name=master_key_group_id]', form).val();

        window.DxCrypto.clearCryptoCache();

        window.DxCrypto.generateMasterKey(masterKeyGroupId, userId, function (wrappedMasterKey) {
            var masterKeyHex = window.DxCrypto.arrayBufferToHexString(wrappedMasterKey);

            $('input[name=master_key]', form).val(masterKeyHex);

            hide_page_splash(1);

            btnSave.data('is-masterkey-generated', 1);

            btnSave.click();
        });
    }
});

window.DxCrypto = new window.DxCryptoClass();
