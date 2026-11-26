<!-- Global Reusable Confirmation Modal (Alpine.js & Tailwind) -->
<div x-data="{
    open: false,
    title: 'Konfirmasi Hapus Data',
    message: 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini bersifat permanen dan tidak dapat dibatalkan.',
    confirmText: 'Ya, Hapus Sekarang',
    cancelText: 'Batal',
    confirmButtonClass: 'bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/20',
    iconClass: 'fa-solid fa-trash-can text-rose-600',
    iconBgClass: 'bg-rose-100 dark:bg-rose-950/60 ring-8 ring-rose-50 dark:ring-rose-900/30',
    targetForm: null,
    callback: null,
    showConfirm(options) {
        this.title = options.title || 'Konfirmasi Hapus Data';
        this.message = options.message || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini bersifat permanen dan tidak dapat dibatalkan.';
        this.confirmText = options.confirmText || 'Ya, Hapus Sekarang';
        this.cancelText = options.cancelText || 'Batal';
        this.confirmButtonClass = options.confirmButtonClass || 'bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/20';
        this.iconClass = options.iconClass || 'fa-solid fa-trash-can text-rose-600';
        this.iconBgClass = options.iconBgClass || 'bg-rose-100 dark:bg-rose-950/60 ring-8 ring-rose-50 dark:ring-rose-900/30';
        this.targetForm = options.form || null;
        this.callback = options.callback || null;
        this.open = true;
    },
    closeModal() {
        this.open = false;
        this.targetForm = null;
        this.callback = null;
    },
    doConfirm() {
        let form = this.targetForm;
        const cb = this.callback;
        this.closeModal();
        if (typeof form === 'string') {
            form = document.getElementById(form);
        }
        if (form && typeof form.submit === 'function') {
            try {
                form.submit();
            } catch (e) {
                HTMLFormElement.prototype.submit.call(form);
            }
        } else if (form) {
            HTMLFormElement.prototype.submit.call(form);
        } else if (typeof cb === 'function') {
            cb();
        }
    }
}"
@open-confirm-modal.window="showConfirm($event.detail)"
x-show="open"
x-cloak
@keydown.escape.window="closeModal()"
class="fixed inset-0 z-[9999] overflow-y-auto"
style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100 dark:border-slate-800 p-6"
             x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.stop>
             
            <div class="text-center sm:text-left sm:flex sm:items-start gap-4">
                <!-- Warning / Delete Icon -->
                <div class="mx-auto flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl transition-all duration-300 sm:mx-0"
                     :class="iconBgClass">
                     <i :class="iconClass" class="text-2xl"></i>
                </div>

                <div class="mt-3 text-center sm:mt-0 sm:text-left flex-1">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight" x-text="title"></h3>
                    <p class="mt-2 text-xs text-slate-600 dark:text-slate-300 leading-relaxed" x-text="message"></p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <button type="button" 
                        @click="closeModal()" 
                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer">
                    <span x-text="cancelText"></span>
                </button>
                <button type="button" 
                        @click="doConfirm()" 
                        :class="confirmButtonClass"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-black transition cursor-pointer flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-check mr-1 text-sm"></i>
                    <span x-text="confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>
