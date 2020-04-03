<div class="modal fade" id="fullModal" tabindex="-1" role="dialog" aria-labelledby="fullModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullModalLabel">短網址</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post" class="">
                    @csrf
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
                        <label for="image" class="col-form-label">og:image</label>
                        <textarea class="form-control" id="image" name="image" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ga_id" class="col-form-label">ga id</label>
                        <input type="text" class="form-control form-control-lg" id="ga_id" name="ga_id" placeholder="ga id">
                    </div>
                    <div class="form-group">
                        <label for="pixel_id" class="col-form-label">pixel id</label>
                        <input type="text" class="form-control form-control-lg" id="pixel_id" name="pixel_id" placeholder="pixel id">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                <button type="button" class="btn btn-primary">儲存</button>
            </div>
        </div>
    </div>
</div>