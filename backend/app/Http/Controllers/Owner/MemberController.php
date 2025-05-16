<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Member\IndexGet;
use App\Models\Member;
use App\Services\Owner\MemberService;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService $memberService,
    ) {}

    public function index(IndexGet $request): JsonResponse
    {
        $members = $this->memberService->index($request->validated());

        return response()->json([
            'members' => $members->map(fn (Member $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'has_reservation' => $member->hasReservation(),
            ]),
        ]);
    }
}
