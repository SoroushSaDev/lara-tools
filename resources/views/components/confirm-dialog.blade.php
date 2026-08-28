{{-- Global confirmation dialog driven by [data-confirm] elements --}}
<div id="confirm-dialog" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" data-confirm-cancel></div>

    <div class="glass relative w-full max-w-sm p-6 text-center animate-pop-in" role="alertdialog" aria-modal="true">
        <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-rose-500/15 text-2xl text-rose-500">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </span>

        <h2 class="heading mt-4 text-lg">Are you sure?</h2>
        <p id="confirm-dialog-message" class="mt-1.5 text-sm font-semibold text-slate-500 dark:text-slate-400"></p>

        <div class="mt-6 flex justify-center gap-3">
            <button type="button" class="btn-ghost flex-1" data-confirm-cancel>Cancel</button>
            <button type="button" id="confirm-dialog-accept" class="btn-danger flex-1">Confirm</button>
        </div>
    </div>
</div>
