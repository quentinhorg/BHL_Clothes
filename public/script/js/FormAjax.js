
class FormAjax{

    envoyerPOST(submitName, postValue, action){
        event;
        var request;
        event.preventDefault();
        
        if (request) {
            request.abort();
        }

        request = $.ajax({
            url: action,
            type: "post",
            data: submitName+"=Ok&"+postValue
        });
     }
     
     envoyerFormulairePOST(formId, postValueExtra,submitName, action){
        var request;
   
     
          var $form = $("#"+formId);
          var $inputs = $form.find("input, select, textarea");
      
          var serializedData = $form.serialize();
          var namePostSubmit = "&"+submitName+"=Ok";
     
      
          request = $.ajax({
              url: action,
              type: "post",
              data: serializedData+namePostSubmit+"&"+postValueExtra
          });
          request.done(function (response, textStatus, jqXHR){
              console.log("Ajax Form - Les données ont bien été sauvegardés");
           
          });
      
          request.fail(function (jqXHR, textStatus, errorThrown){
              console.error(
                  "Ajax Form - Une erreur est survenu lors de la sauvegarder: "+
                  textStatus, errorThrown
              );
          });
     }
     
}
