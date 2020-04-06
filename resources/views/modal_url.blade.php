<div class="modal fade" id="fullModal" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="fullModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullModalLabel">短網址</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="url_form" action="" method="post" class="">
                    @csrf
                    <input type="hidden" id="code" name="code">
                    <div class="form-group">
                        <label for="url" class="col-form-label">網址</label>
                        <input type="url" class="form-control form-control-lg" id="url" name="url" placeholder="網址">
                    </div>
                    <div class="form-group">
                        <label for="title" class="col-form-label">og:title</label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" placeholder="og:title">
                    </div>
                    <div class="form-group">
                        <label for="description" class="col-form-label">og:description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="hidden" id="image" name="image">
                        <label for="image_block" class="col-form-label">og:image</label>
                        <div class="position-relative" id="image_block" ondragover="dragover_handler(event);">
                            <button id="remove_image_file" type="button" class="close d-none">
                                <span aria-hidden="true">×</span>
                            </button>
                            <input type="file" name="image_file" id="image_file" accept="image/*" class="d-none">
                            <div class="text-center">
                                <img id="pre_image" width="200" heigh="200" src="" class="rounded" alt="">
                            </div>
                            <div id="text_block" class="position-absolute" style="top: 50%;left: 45%;transform: translate(-50%, -50%);">
                                Drag and drop a file here or click
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ga_id" class="col-form-label">ga id</label>
                        <input type="text" class="form-control form-control-lg" id="ga_id" name="ga_id" placeholder="ga id">
                    </div>
                    <div class="form-group">
                        <label for="pixel_id" class="col-form-label">pixel id</label>
                        <input type="text" class="form-control form-control-lg" id="pixel_id" name="pixel_id" placeholder="pixel id">
                    </div>
                    <div class="form-group">
                        <label for="hash_tag" class="col-form-label">hash tag</label>
                        <textarea class="form-control" id="hash_tag" name="hash_tag" rows="3" placeholder="請用半型逗號(,)隔開"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                <button type="button" id="send" class="btn btn-primary">儲存</button>
            </div>
        </div>
    </div>
</div>