<div class="modal fade" id="deleteModal" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">刪除短網址</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="delete_form" action="" method="post" class="">
                    @csrf
                    <input type="hidden" id="delete_code" name="code">
                    <div class="">
                        <p>您確定要刪除</p>
                        <p id="del_short_text"></p>
                        <p>的資料?</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                <button type="button" id="delete_btn" class="btn btn-danger">刪除</button>
            </div>
        </div>
    </div>
</div>