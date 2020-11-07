<div class="container">
<div class="row message-wrapper rounded shadow mb-20">
    
    <div class="col-md-8 message-sideright">
        <div class="panel">
            <div class="panel-heading">
                <div class="media">
                    
                    
                    <div class="media-body">
                        <h4 class="media-heading"><?php echo $contactInfo->nom(); ?></h4>
                        <small><?php echo $contactInfo->email(); ?></small> <br>
                        <small><?php echo $contactInfo->date('d/m/Y à H\hi'); ?></small>
                    </div>
                </div>
            </div><!-- /.panel-heading -->
            <div class="panel-body">
                <p class="lead">
                    Objet: <?php echo $contactInfo->sujet(); ?>
                </p>
                <hr>
                <p>
                  <?php echo $contactInfo->message(); ?>
                </p>

                <hr>

                <div class="media mt-3">
                      <div class="media-body">
                          <textarea class="wysihtml5 form-control" rows="9" placeholder="Répondre ici..."></textarea>
                      </div>
                  </div>
                  <div class="text-right">
                      <button type="button" class="btn btn-primary waves-effect waves-light mt-3"><i class="fa fa-send mr-1"></i> Envoyer</button>
                  </div>
               
                
            </div><!-- /.panel-body -->
        </div><!-- /.panel -->
        
    </div><!-- /.message-sideright -->
</div>
</div>