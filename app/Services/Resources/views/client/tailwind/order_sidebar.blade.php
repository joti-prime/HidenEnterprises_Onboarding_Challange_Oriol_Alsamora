<div class="px-3 rounded py-4 overflow-y-auto bg-gray-50 dark:bg-gray-800 mb-6">
    <ul class="space-y-2 font-medium">
        <li>
            <a href="{{ route('cf.edit.domain', $order->id) }}"
               class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ is_active('cf.edit.domain', [], 'bg-gray-100 dark:bg-gray-700') }}">
                        <span class="flex-shrink-0 flex w-6 h-6 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                style="font-size: 23px;"> <i class='bx bxs-cloud'></i> </span>
                <span class="flex-1 ml-3 whitespace-nowrap">{!! __('client.domain') !!}</span>
            </a>
        </li>
    </ul>
</div>



