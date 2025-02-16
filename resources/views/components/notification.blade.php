<div
    x-data="{
        show: false,
        message: '',
        type: 'success'
    }"
    x-show="show"
    x-transition
    x-on:notify.window="
        show = true;
        message = $event.detail.message;
        type = $event.detail.type;
        setTimeout(() => { show = false }, 3000);
    "
    class="fixed top-4 right-4 z-50"
>
    <div x-bind:class="{
        'bg-green-100 border-green-400 text-green-700': type === 'success',
        'bg-red-100 border-red-400 text-red-700': type === 'error'
    }" class="border px-4 py-3 rounded relative" role="alert">
        <span x-text="message" class="block sm:inline"></span>
    </div>
</div>
