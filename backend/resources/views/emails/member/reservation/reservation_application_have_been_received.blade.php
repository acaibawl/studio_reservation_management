@php
    use App\Models\Member;
    /** @var Member $member */
    use App\Models\Reservation;
    /** @var Reservation $reservation */
@endphp

オーナー様

会員ID: {{ $member->id }} の
{{ $member->name }} 様のスタジオ予約申請が受理されました。

予約日時： {{ $reservation->start_at->isoFormat('Y年M月D日(ddd)') }}
スタジオ： {{ $reservation->studio->name }}

開始時間： {{ $reservation->start_at->format('H:i') }}
利用時間： {{ $reservation->usage_hour }} 時間

よろしくお願いいたします。

{{ config('app.front_base_url') }}/owner/login
