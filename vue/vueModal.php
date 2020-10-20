<!-- Modal -->
<div id="popup" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <!-- <div class="modal-footer">
       
      </div> -->
    </div>
  </div>
</div>
    

<script>


    function popup(titre, message, redirectionAuto){
        $("#popup").find("h5.modal-title").html(titre);
        $("#popup").find("div.modal-body").html(message);

        if(redirectionAuto){
            $("#popup").modal({
                backdrop: 'static',
                keyboard: false
            });
            $("#popup").find(".modal-body").after("<div class='modal-footer'> Redirection automatique... </div>") ;
            $("#popup").find("button.close").css("display", "none");
        }
       
        $("#popup").modal('show');
    }
</script>