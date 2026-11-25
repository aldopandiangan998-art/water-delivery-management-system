<!-- Toast Feedback Container (Alpine.js driven via session flash & window events) -->
<div 
    x-data="toastManager()" 
    @toast.window="addToast($event.detail)"
    class="fixed bottom-5 right-5 z-50 flex flex-col space-y-2 pointer-events-none max-w-sm w-full px-4 sm:px-0">
    
    <template x-for="t in toasts" :key="t.id">
        <div 
            x-show="t.visible"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="pointer-events-auto p-4 rounded-2xl shadow-xl border flex items-center space-x-3 text-xs sm:text-sm font-semibold transition"
            :class="{
                'bg-emerald-600 text-white border-emerald-500': t.type === 'success',
                'bg-rose-600 text-white border-rose-500': t.type === 'error',
                'bg-amber-500 text-slate-950 border-amber-400': t.type === 'warning',
                'bg-slate-900 text-white border-slate-800 dark:bg-slate-800': t.type === 'info'
            }">
            
            <i :class="{
                'fa-solid fa-circle-check text-base': t.type === 'success',
                'fa-solid fa-circle-exclamation text-base': t.type === 'error',
                'fa-solid fa-triangle-exclamation text-base': t.type === 'warning',
                'fa-solid fa-circle-info text-base': t.type === 'info'
            }"></i>
            
            <span class="flex-1" x-text="t.message"></span>
            
            <button type="button" @click="dismiss(t.id)" class="opacity-75 hover:opacity-100 transition p-1">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
    </template>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        init() {
            @if (session('success'))
                this.addToast({ message: "{{ session('success') }}", type: 'success' });
            @endif
            @if (session('error'))
                this.addToast({ message: "{{ session('error') }}", type: 'error' });
            @endif
            @if (session('info'))
                this.addToast({ message: "{{ session('info') }}", type: 'info' });
            @endif
        },
        addToast(detail) {
            const id = Date.now() + Math.random();
            const toast = {
                id,
                message: typeof detail === 'string' ? detail : detail.message,
                type: detail.type || 'info',
                visible: true,
            };
            this.toasts.push(toast);
            setTimeout(() => this.dismiss(id), 4000);
        },
        dismiss(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                this.toasts[index].visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 200);
            }
        }
    };
}
</script>
