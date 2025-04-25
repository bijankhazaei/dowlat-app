<x-filament::page>
    <div class="mb-4 p-4 bg-gray-100 rounded-xl">
        <p>اعتبار فعلی شما: {{ number_format(auth()->user()->credit) }} تومان</p>
    </div>

    <form wire:submit.prevent="submit">
        @foreach ($days as $day)
            <x-filament::card>
                <div class="font-bold mb-2">{{ $day['label'] }}</div>

                @foreach ($day['meals'] as $meal)
                    <x-filament::input.checkbox
                        name="selectedMeals.{{ $day['date'] }}.{{ $meal->id }}"
                        label="{{ $meal->meal_type }}: {{ $meal->title }} ({{ number_format($meal->price) }} تومان)"
                    />
                    {{ $meal->meal_type }}: {{ $meal->title }} ({{ number_format($meal->price) }} تومان)
                @endforeach
            </x-filament::card>
        @endforeach

        <x-filament::button type="submit" class="mt-4">ثبت رزرو</x-filament::button>
    </form>
</x-filament::page>
