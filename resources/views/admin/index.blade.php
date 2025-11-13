@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-center">Панель администратора</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- ========== ФОРМА ДОБАВЛЕНИЯ БРОНИ С ЛЕНТОЙ ВРЕМЕНИ ========== --}}
    <h4 class="mb-3">Добавить бронирование</h4>
    <form method="POST" action="{{ route('admin.add') }}" class="mb-4 border p-3 rounded" id="adminAddForm">
        @csrf
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Пользователь</label>
                <select name="user_id" class="form-select" required>
                    <option value="">Выберите...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Квест</label>
                <select name="quest_id" id="questSelect" class="form-select" required>
                    <option value="">Выберите...</option>
                    @foreach($quests as $quest)
                        <option value="{{ $quest->id }}">{{ $quest->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Дата</label>
                <input type="date" name="date" id="dateInput" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Игроков</label>
                <input type="number" name="players_count" class="form-control" min="1" value="2" required>
            </div>

            {{-- Время (скрытое поле) --}}
            <input type="hidden" name="time" id="selectedTime" required>
        </div>

        {{-- Лента времени --}}
        <div class="mt-3">
            <label class="form-label fw-bold">Время</label>
            <div id="slotsContainer" class="timeline-wrap">
                <div class="text-muted small py-2">Сначала выберите квест и дату</div>
            </div>
        </div>

        <button type="submit" class="btn btn-success mt-3">Добавить бронь</button>
    </form>

    {{-- ========== СПИСОК БРОНИРОВАНИЙ ========== --}}
    <h4 class="mb-3">Все бронирования</h4>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Квест</th>
                    <th>Дата</th>
                    <th>Время</th>
                    <th>Игроков</th>
                    <th>Статус</th>
                    <th>Стоимость</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $b)
                <tr>
                    <td>{{ $b->id }}</td>
                    <td>{{ $b->user_name }}</td>
                    <td>{{ $b->quest_title }}</td>
                    <td>{{ $b->date }}</td>
                    <td>{{ $b->time }}</td>
                    <td>{{ $b->players_count }}</td>
                    <td>
                        @if($b->status === 'paid')
                            <span class="badge bg-success">Оплачено</span>
                        @elseif($b->status === 'canceled')
                            <span class="badge bg-secondary">Отменено</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ $b->status }}</span>
                        @endif
                    </td>
                    <td>{{ $b->total_price }} ₽</td>
                    <td>
                        @if($b->status === 'paid')
                            <form method="POST" action="{{ route('admin.cancel', $b->id) }}" onsubmit="return confirm('Отменить бронь?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">Отменить</button>
                            </form>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ====== Стили таймлайна ====== --}}
<style>
.timeline-wrap {
    display: flex; flex-wrap: wrap; gap: .5rem;
    padding: .75rem; background:#f8f9fa; border-radius: .5rem; min-height: 62px;
    border:1px solid #e9ecef;
}
.slot-btn {
    border: none; padding: 8px 14px; border-radius: 8px;
    font-weight: 600; cursor: pointer; min-width: 68px; transition: .15s ease-in-out;
}
.slot-free   { background:#28a745; color:#fff; }
.slot-free:hover { filter: brightness(0.95); }
.slot-taken  { background:#adb5bd; color:#444; cursor: not-allowed; }
.slot-expired{ background:#6c757d; color:#ddd; cursor: not-allowed; }
.slot-active { background:#0d6efd !important; color:#fff; }
</style>

{{-- ====== Логика подгрузки слотов и выбора времени ====== --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const questSel = document.getElementById('questSelect');
    const dateInp  = document.getElementById('dateInput');
    const slotsBox = document.getElementById('slotsContainer');
    const timeHidden = document.getElementById('selectedTime');

    // минимальная дата — сегодня
    const today = new Date().toISOString().split('T')[0];
    dateInp.min = today;

    // загрузка слотов
    async function loadSlots() {
        timeHidden.value = '';
        const questId = questSel.value;
        const date    = dateInp.value;

        if (!questId || !date) {
            slotsBox.innerHTML = '<div class="text-muted small py-2">Сначала выберите квест и дату</div>';
            return;
        }

        slotsBox.innerHTML = '<div class="text-muted small">Загрузка слотов...</div>';

        try {
            const res  = await fetch(`/slots/${questId}/${date}`);
            const data = await res.json();

            slotsBox.innerHTML = '';
            if (!data.slots || data.slots.length === 0) {
                slotsBox.innerHTML = '<div class="text-muted small">На эту дату слотов нет</div>';
                return;
            }

            // минуты текущего времени
            const now = new Date();
            const isToday = (date === today);
            const curMinutes = now.getHours() * 60 + now.getMinutes();

            data.slots.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'slot-btn';
                btn.textContent = slot;

                const [hh, mm] = slot.split(':').map(Number);
                const slotMinutes = hh * 60 + mm;

                const isTaken   = data.taken && data.taken.includes(slot);
                const isExpired = isToday && slotMinutes <= curMinutes;

                if (isTaken) {
                    btn.classList.add('slot-taken'); btn.disabled = true;
                } else if (isExpired) {
                    btn.classList.add('slot-expired'); btn.disabled = true;
                } else {
                    btn.classList.add('slot-free');
                    btn.addEventListener('click', () => {
                        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('slot-active'));
                        btn.classList.add('slot-active');
                        timeHidden.value = slot;
                    });
                }

                slotsBox.appendChild(btn);
            });

        } catch (e) {
            console.error(e);
            slotsBox.innerHTML = '<div class="text-danger small">Ошибка загрузки слотов</div>';
        }
    }

    questSel.addEventListener('change', loadSlots);
    dateInp.addEventListener('change', loadSlots);
});
</script>
@endsection
