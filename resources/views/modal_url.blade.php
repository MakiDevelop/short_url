<div class="modal fade" id="fullModal" tabindex="-1" data-bs-backdrop="static" role="dialog" aria-labelledby="fullModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullModalLabel">短網址</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="url_form" action="" method="post" class="">
                    @csrf
                    <input type="hidden" id="code" name="code">
                    <input type="hidden" id="content_type" name="content_type">
                    <div class="form-group">
                        <label for="url" class="col-form-label">目的網址</label>
                        <input type="url" class="form-control form-control-lg" id="url" name="url" placeholder="例：https://www.techbang.com/posts/78218">
                    </div>
                    <div class="form-group">
                        <label for="title" class="col-form-label">og:title</label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" placeholder=" ">
                    </div>
                    <div class="form-group">
                        <label for="description" class="col-form-label">og:description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="hidden" id="image" name="image">
                        <label for="image_file" class="col-form-label">og:image</label>
                        <div class="position-relative" id="image_block" ondragover="dragover_handler(event);" style="z-index: 100;">
                            <button id="remove_image_file" type="button" class="close d-none">
                                <span aria-hidden="true">×</span>
                            </button>
                            <div class="text-center">
                                <img id="pre_image" width="200" heigh="200" src="" class="rounded" alt="">
                            </div>
                            <div id="text_block" class="position-absolute" style="top: 50%;left: 45%;transform: translate(-50%, -50%);">
                                拖曳圖片至此或點擊上傳圖片
                            </div>
                            <input type="file" name="image_file" id="image_file" accept="image/*" class="d-none">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ga_id" class="col-form-label">GA ID</label>
                        <input type="text" class="form-control form-control-lg" id="ga_id" name="ga_id" placeholder="UA-XXXXXXXX-X">
                    </div>
                    <div class="form-group">
                        <label for="pixel_id" class="col-form-label">pixel id</label>
                        <input type="text" class="form-control form-control-lg" id="pixel_id" name="pixel_id" placeholder="pixel id">
                    </div>

                    <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseUtm" role="button" aria-expanded="false" aria-controls="collapseUtm">
                        UTM設定
                    </a>

                    <div class="collapse" id="collapseUtm">
                        <div class="form-group">
                            <label for="source" class="col-form-label">utm source</label>
                            <input type="text" class="form-control form-control-lg" id="source" name="source" placeholder="utm source">
                        </div>
                        <div class="form-group">
                            <label for="medium" class="col-form-label">utm medium</label>
                            <input type="text" class="form-control form-control-lg" id="medium" name="medium" placeholder="utm medium">
                        </div>
                        <div class="form-group">
                            <label for="campaign" class="col-form-label">utm campaign</label>
                            <input type="text" class="form-control form-control-lg" id="campaign" name="campaign" placeholder="utm campaign">
                        </div>
                        <div class="form-group">
                            <label for="term" class="col-form-label">utm term</label>
                            <input type="text" class="form-control form-control-lg" id="term" name="term" placeholder="utm term">
                        </div>
                        <div class="form-group">
                            <label for="content" class="col-form-label">utm content</label>
                            <input type="text" class="form-control form-control-lg" id="content" name="content" placeholder="utm content">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="hash_tag" class="col-form-label">標籤</label>
                        <textarea class="form-control" id="hash_tag" name="hash_tag" rows="3" placeholder="請用半型逗號(,)隔開"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" id="send" class="btn btn-primary">儲存</button>
            </div>
        </div>
    </div>
</div>