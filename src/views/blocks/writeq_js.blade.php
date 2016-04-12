<script type='text/javascript'>
    function is_email (email) 
    {
        var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }
    
    $('#email_btn_{{ $block_guid }}').click(function(e) {
        $('#popup_email_{{ $block_guid }}').modal('hide');
        
        var quest = $("#writeq_{{ $block_guid }}").find("[name=askQuestion]").val();
        save_question(quest, $('#popup_email_{{ $block_guid }}').find("input[name=q_email]").val());
    });
                        
    $("#writeq_{{ $block_guid }}").find("button").click(function(){
        var quest = $("#writeq_{{ $block_guid }}").find("[name=askQuestion]").val();
        
        if (quest.length == 0)
        {
            notify_err("Lūdzu, ievadiet jautājuma/ziņojuma tekstu!");
            $("#writeq_{{ $block_guid }}").find("[name=askQuestion]").focus();
            return;
        }
        
        if ($("#writeq_{{ $block_guid }}").find("input[type=checkbox]").is(':checked'))
        {
            $('#popup_email_{{ $block_guid }}').on('shown.bs.modal', function () {			
                $('#popup_email_{{ $block_guid }}').find("input[name=q_email]").focus();                          	
            });
            
            $('#popup_email_{{ $block_guid }}').validator({
                custom : {
                    foo: function($el) 
                    {                        
                        if (!is_email($el.val()))
                        {
                            return false;
                        }
                        return true;
                    }
                },
                errors: {
                    foo: 'Nav norādīta korekta e-pasta adrese!',
                    auto: 'Nav norādīta vērtība!'
                },
                feedback: {
                    success: 'glyphicon-ok',
                    error: 'glyphicon-alert'
                }
            });
            $('#popup_email_{{ $block_guid }}').modal('show');
            
            return;
        }        
        
       
        save_question(quest, '');
    });
    
    function save_question(question, email)
    {
         $('#popup_window .modal-dialog').removeClass('modal-lg').addClass('modal-md');
        
        $('#popup_window').on('hidden.bs.modal', function (e) {			
            $('#popup_window .modal-dialog').removeClass('modal-md').addClass('modal-lg');                             	
        });
        
        $('#popup_window .modal-title').html("Jautājuma nosūtīšana");
        $("#popup_body").html('<div style="padding: 10px;"><img src="{{Request::root()}}/img/loading.gif" alt="Lūdzu, uzgaidiet..." title="Lūdzu, uzgaidiet..." />Notiek jautājuma nosūtīšana portāla administrācijai... Lūdzu, uzgaidiet...</div>');
        $('#popup_window').modal('show');
        
        var formData = "param=" + encodeURIComponent("OBJ=WRITEQ|SOURCE={{ $source_id}}") + "&question=" + encodeURIComponent(question) + "&email=" + email;
        
        var ajax_url = 'block_ajax';

        $("#load_more_link").text("IELĀDĒ...");

        var request = new FormAjaxRequestIE9 (ajax_url, "", "", formData);            
        request.progress_info = "Saglabā datus. Lūdzu, uzgaidiet...";                       

        request.callback = function(data) {
            
            $('#popup_window .modal-title').html("Jautājums nosūtīts");
            $("#popup_body").html('<div style="padding: 10px;">Jūsu jautājums ir nosūtīts portāla administrācijai.</div>');
            $("#writeq_{{ $block_guid }}").find("[name=askQuestion]").val('');           

        };
        
        request.err_callback = function(err_txt) {
            $('#popup_window .modal-title').html("Kļūda");
            $("#popup_body").html('<div style="padding: 10px;"><font color="red">' + err_txt + '</font></div>');
        };

        // izpildam AJAX pieprasījumu
        request.doRequest();
    }
    
</script>