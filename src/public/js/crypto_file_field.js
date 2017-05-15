(function ($)
{
    /**
     * Creates jQuery plugin for crypto fields
     * @returns DxCryptoFileField
     */
    $.fn.DxCryptoFileField = function ()
    {
        /*for (var i=0; i < this.length; i++){
         var selfR = this[i];
         
         var self = $(selfR);
         
         if (self.data('dx_is_init') == 1) {
         continue;
         }
         
         self.data('dx_is_init', 1);
         
         var cr = new $.DxCryptoFileField(self);
         
         this.crypto = cr;
         }*/

        return this.each(function ()
        {
            var self = $(this);

            if (self.data('dx_is_init') == 1) {
                return;
            }

            self.data('dx_is_init', 1);

            this.crypto = new $.DxCryptoFileField(self);
        });
    };

    /**
     * Class for managing crypto fields
     * @type DxCryptoFileField 
     */
    $.DxCryptoFileField = function (domObject) {
        /**
         * Field's DOM object which is related to this class
         */
        this.domObject = domObject;

        // Initializes class
        this.init();
    };

    /**
     * Initializes component
     * @returns {undefined}
     */
    $.extend($.DxCryptoFileField.prototype, {
        /**
         * Initializes field
         * @returns {undefined}
         */
        init: function () {
            var self = this;

            if (this.domObject.is('input')) {
                this.inputInit();
            } else if (this.domObject.is('a')) {
                this.linkInit();
            }
        },
        inputInit: function () {

        },
        linkInit: function () {
            this.domObject.click(this.onLinkClick);
        },
        onLinkClick: function (event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            window.DxCrypto.decryptFields($(this));
        },
        setAccessError: function () {
            /*  var label = '<span class="label label-danger"> ' + Lang.get('crypto.e_no_access') + ' </span>';
             
             this.domObject.next('.dx-crypto-decrypt-btn').remove();
             this.domObject.after(label);*/
        },
        /**
         * Gets value of current element. It can be input or other container (e.g. div, span)
         * @returns {string}
         */
        getValue: function (callback) {
            if (this.domObject.is('input')) {
                this.getFileValue(callback);
            } else if (this.domObject.is('a')) {
                this.getLinkValue(callback);
            }
        },
        getFileValue: function (callback) {
            if (this.domObject[0].files.length === 0) {
                return new ArrayBuffer(0);
            }

            var fr = new FileReader();
            fr.onload = function () {
                var data = fr.result;
                var dataArray = new Uint8Array(data);

                callback(dataArray);
            };

            fr.readAsArrayBuffer(this.domObject[0].files[0]);
        },
        getLinkValue: function (callback) {
            var xhr = new XMLHttpRequest();

            xhr.onload = function () {
                var reader = new FileReader();

                reader.readAsArrayBuffer(xhr.response);

                reader.onloadend = function () {

                    var arrayBuffer = new Uint8Array(reader.result);

                    callback(arrayBuffer, xhr.response.type);
                };
            };
            xhr.open('GET', this.domObject.attr("href"));
            xhr.responseType = 'blob';
            xhr.send();
        },
        /**
         * Sets value of current element. It can be input or other container (e.g. div, span)
         * @param {string} value It will be set to element
         * @returns {undefined}
         */
        setValue: function (value, fileType) {
            if (this.domObject.is('input')) {
                return this.setFileValue(value);
            } else if (this.domObject.is('a')) {
                return this.setLinkValue(value, fileType);
            }
        },
        setFileValue: function (value) {
            var valueBlob = new Blob([new Uint8Array(value)], {type: "application/octet-stream"});

            this.domObject.data('crypto-value', valueBlob);
        },
        setLinkValue: function (value, fileType) {
            var blob = new Blob([value], {type: fileType});
            var newUrl = URL.createObjectURL(blob);

            var a = document.createElement("a");
            a.style.display = "none";
            a.href = newUrl;
            a.download = this.domObject.text().trim();
            $("body").append($(a));
            a.click();
            window.URL.revokeObjectURL(newUrl);
            a.remove();
        }
    });
})(jQuery);

// ajaxComplete ready
$(document).ready(function () {
    // Initializes all found worklfow containers
    $('.dx-crypto-field-file').DxCryptoFileField();
});

$(document).ajaxComplete(function () {
    // Initializes all found worklfow containers
    $('.dx-crypto-field-file').DxCryptoFileField();
});
