<div id="new-message-dropzone" class="post-dropzone">
    <?php echo form_open(get_uri("messages/edit_message"), array("id" => "message-form", "class" => "general-form", "role" => "form")); ?>
    <div class="modal-body clearfix">
        <div class="container-fluid">
            <input type="hidden" name="id" value="<?php echo $model_info->id ?? 0; ?>" />
            <div class="form-group">
                <div class="col-md-12">
                    <?php
                    echo form_textarea(array(
                        "id" => "message",
                        "name" => "message",
                        "class" => "form-control",
                        "placeholder" => app_lang('write_a_message'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                        "style" => "min-height:200px;",
                        "value" => $model_info->message,
                        "data-rich-text-editor" => true
                    ));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <?php echo view("includes/dropzone_preview"); ?> 
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-default upload-file-button float-start me-auto btn-sm round" type="button" style="color:#7988a2"><i data-feather='camera' class='icon-16'></i> <?php echo app_lang("upload_file"); ?></button>
        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        <button type="submit" class="btn btn-primary"><span data-feather="send" class="icon-16"></span> <?php echo app_lang('send'); ?></button>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var uploadUrl = "<?php echo get_uri("messages/upload_file"); ?>";
        var validationUrl = "<?php echo get_uri("messages/validate_message_file"); ?>";

        var dropzone = attachDropzoneWithForm("#new-message-dropzone", uploadUrl, validationUrl);

        <?php if (!empty($model_info->files)) : ?>
            var existingFiles = <?php echo json_encode(unserialize($model_info->files)); ?>; // Converte os arquivos existentes em JSON
           
            existingFiles.forEach(function(file) {
                var mockFile = { name: file.file_name, size: file.size }; // Cria um objeto mock do arquivo
                // dropzone.emit("addedfile", mockFile);  // Simula que o arquivo foi adicionado ao Dropzone
                
                // // Ajuste o caminho da miniatura se necessário
                <?php $target_path = get_setting("timeline_file_path");?>
                var thumbnailUrl = "<?php echo get_uri($target_path); ?>" + file.file_name;
                
                // // Se o arquivo for uma imagem, carregue a miniatura
                // if (file.is_image) {  // Supondo que exista uma propriedade 'is_image' indicando se é uma imagem
                //     dropzone.emit("thumbnail", mockFile, thumbnailUrl); // Exibe a miniatura
                // }
                
                // dropzone.emit("complete", mockFile);  // Marca o arquivo como completo
                // dropzone.files.push(mockFile);  // Adiciona o arquivo à lista de arquivos do Dropzone
                dropzone.displayExistingFile(mockFile, thumbnailUrl);

            });
        <?php endif; ?>

        $("#message-form").appForm({
            onSuccess: function (result) {

                appAlert.success(result.message, {duration: 10000});

                location.reload();

                //we'll check if the single user chat list is open. 
                //if so, we'll assume that, this message created from the view.
                //and we'll open the chat automatically.
                if ($("#js-single-group-chat-list").is(":visible") && typeof window.triggerActiveChat !== "undefined") {
                    setTimeout(function () {
                        window.triggerActiveChat(result.id);
                    }, 1000);
                }

            }
        });

        $("#message-form .select2").select2();
    });
</script>    