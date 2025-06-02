<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Member\IndexGet;
use App\Http\Resources\Onwer\Member\MemberCollection;
use App\Models\Member;
use App\Models\Reservation;
use App\Services\Owner\MemberService;
use Arr;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService $memberService,
    ) {}

    public function index(IndexGet $request): JsonResponse
    {
        $attributes = $request->validated();
        [$members, $pageSize] = $this->memberService->fetchPaginatedMembers($attributes);

        return response()->json([
            'members' => new MemberCollection($members),
            'page_size' => $pageSize,
            'current_page' => (int) Arr::get($attributes, 'page', 1),
        ]);
    }

    public function show(Member $member): JsonResponse
    {
        $reservations = $this->memberService->getFutureReservations($member);

        return response()->json([
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'address' => $member->address,
                'tel' => $member->tel,
                'reservations' => $reservations->map(fn (Reservation $reservation) => [
                    'id' => $reservation->id,
                    'member_id' => $reservation->member_id,
                    'studio_id' => $reservation->studio_id,
                    'studio_name' => $reservation->studio->name,
                    'start_at' => $reservation->start_at->format('Y-m-d H:i:s'),
                    'finish_at' => $reservation->finish_at->format('Y-m-d H:i:s'),
                    'memo' => $reservation->memo,
                ]),
            ],
        ]);
    }
}
