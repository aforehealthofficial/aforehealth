<!-- AFOREHEALTH Custom Confirmation Modal -->
<div class="modal fade" id="aforeConfirmModal" tabindex="-1" aria-labelledby="aforeConfirmTitle" aria-hidden="true" style="z-index: 1095;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: #ffffff;">
            <div class="modal-body p-4 text-center">
                <div id="aforeConfirmIconWrap" class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 72px; height: 72px; transition: transform 0.3s ease;">
                    <i id="aforeConfirmIcon" class="fs-1"></i>
                </div>
                <h5 id="aforeConfirmTitle" class="fw-bold text-dark mb-2">Are you sure?</h5>
                <p id="aforeConfirmText" class="text-muted small mb-4 px-2">This action cannot be undone.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-semibold border" data-bs-dismiss="modal" id="aforeConfirmCancelBtn">
                        Cancel
                    </button>
                    <button type="button" class="btn px-4 py-2 rounded-pill fw-semibold shadow-sm" id="aforeConfirmOkBtn">
                        Confirm Action
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
