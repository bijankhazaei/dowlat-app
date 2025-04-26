<x-filament::page>
    <form wire:submit.prevent="submit">
        @foreach ($days as $day)
            <div class="mb-lg-5 mt-6">
                <h3 class="text-sm font-semibold mb-2">
                    {{ $day['dayName'] }} - {{ $day['label'] }}
                </h3>

                {{-- 1) table-fixed + colgroup for widths --}}
                <table class="table-fixed w-full text-sm border-collapse">
                    <colgroup>
                        <col class="w-1/12">
                        <col class="w-2/6">
                        <col class="w-2/6">
                        <col class="w-1/6">
                    </colgroup>
                    <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2 text-right">انتخاب</th>
                        <th class="px-4 py-2 text-right">نوع غذا</th>
                        <th class="px-4 py-2 text-right">عنوان</th>
                        <th class="px-4 py-2 text-right">قیمت</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($day['meals'] as $meal)
                        {{-- 2) Alpine + Livewire entangle --}}
                        @php
                            $dayKey = str_replace('-', '_', $day['date']);
                        @endphp

                        <tr
                            x-data="{ checked: @entangle('selectedMeals.'.$day['date'].'.'.$meal->id) }"
                            :class="{ 'bg-green-100': checked }"
                            class="border-b"
                        >
                            <td class="px-4 py-2 text-right">
                                <x-filament::input.checkbox
                                    x-model="checked"
                                    wire:model.defer="selectedMeals.{{ $day['date'] }}.{{ $meal->id }}"
                                    label=""
                                />
                            </td>
                            <td class="px-4 py-2">
                                {{ [
                                    'main' => 'غذای اصلی',
                                    'side' => 'پیش‌غذا',
                                    'drink' => 'نوشیدنی',
                                ][$meal->meal_type] ?? 'نامشخص' }}
                            </td>
                            <td class="px-4 py-2">{{ $meal->title }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($meal->price) }} تومان</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        <x-filament::button type="submit" class="mt-4">ثبت رزرو</x-filament::button>
    </form>
</x-filament::page>
