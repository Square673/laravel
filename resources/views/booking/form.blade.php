@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-4 text-center">Бронирование квеста: {{ $quest->title }}</h3>

            <p class="text-muted text-center">
                Сложность: {{ $quest->difficulty }} · Длительность: {{ $quest->duration }} мин
            </p>

            {{-- Сообщения --}}
            @if(session('error'))
                <div class="alert alert-danger text-center">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('booking.book', $quest->id) }}" id="bookingForm">
                @csrf

                {{-- Выбор даты --}}
                <div class="mb-4 text-center">
                    <label for="date" class="form-label fw-bold fs-5">Выберите дату</label>
                    <input type="date" name="date" id="date" class="form-control form-control-lg w-50 mx-auto" required>
                </div>

                {{-- Выбор времени (ячейки) --}}
                <div class="mb-4 text-center">
                    <label class="form-label fw-bold fs-5">Выберите время</label>
                    <div id="slotsContainer" class="d-flex flex-wrap justify-content-center gap-2 py-3">
                        <p class="text-muted">Сначала выберите дату</p>
                    </div>
                    <input type="hidden" name="time" id="selectedTime" required>
                </div>

                {{-- Количество игроков --}}
                <div class="mb-3 text-center">
                    <label for="players" class="form-label fw-bold fs-5">Количество игроков</label>
                    <input type="number" name="players" id="players" min="1" value="1" class="form-control w-25 mx-auto text-center" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success btn-lg px-5">Подтвердить</button>
                    <a href="{{ route('quest.show', $quest->id) }}" class="btn btn-outline-secondary btn-lg ms-2 px-4">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Стили ячеек --}}
<style>
    .slot-btn {
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 70px;
    }
    .slot-free {
        background-color: #28a745;
        color: white;
    }
    .slot-free:hover {
        background-color: #218838;
    }
    .slot-taken {
        background-color: #adb5bd;
        color: #444;
        cursor: not-allowed;
    }
    .slot-selected {
        background-color: #007bff !important;
        color: white;
    }
    .slot-expired {
        background-color: #6c757d;
        color: #ccc;
        cursor: not-allowed;
    }
</style>

{{-- JS логика выбора --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');
    const slotsContainer = document.getElementById('slotsContainer');
    const selectedTime = document.getElementById('selectedTime');
    const questId = {{ $quest->id }};

    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;

    // Загрузка доступных слотов
    dateInput.addEventListener('change', async function() {
        const date = this.value;
        selectedTime.value = '';
        slotsContainer.innerHTML = '<p class="text-muted">Загрузка слотов...</p>';

        const res = await fetch(`/slots/${questId}/${date}`);
        const data = await res.json();

        slotsContainer.innerHTML = '';

        if (data.slots.length === 0) {
            slotsContainer.innerHTML = '<p class="text-muted">Нет доступных слотов для этой даты.</p>';
            return;
        }

        // Текущее время для проверки "прошедших слотов"
        const now = new Date();
        const currentTime = now.getHours() * 60 + now.getMinutes(); // минуты с начала дня
        const todayDate = today;

        data.slots.forEach(slot => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.classList.add('slot-btn');

            const [hours, minutes] = slot.split(':').map(Number);
            const slotTime = hours * 60 + minutes;

            const isTaken = data.taken.includes(slot);
            const isExpired = (date === todayDate && slotTime <= currentTime);

            if (isTaken) {
                btn.classList.add('slot-taken');
                btn.textContent = slot + ' (занято)';
                btn.disabled = true;
            } else if (isExpired) {
                btn.classList.add('slot-expired');
                btn.textContent = slot;
                btn.disabled = true;
            } else {
                btn.classList.add('slot-free');
                btn.textContent = slot;
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('slot-selected'));
                    btn.classList.add('slot-selected');
                    selectedTime.value = slot;
                });
            }

            slotsContainer.appendChild(btn);
        });
    });
});
</script>
@endsection
