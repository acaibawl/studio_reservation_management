
export interface ReservationResponse {
  reservation: {
    id: number;
    studio_id: number;
    studio_name: string;
    start_at: string;
    finish_at: string;
    max_usage_hour: number;
    member_id: number;
    member_name: string;
    memo: string;
  }
}

export class Reservation {
  private readonly _id: number;
  private readonly _studioId: number;
  private readonly _studioName: string;
  private readonly _startAt: Date;
  private readonly _finishAt: Date;
  private readonly _maxUsageHour: number;
  private readonly _memberId: number;
  private readonly _memberName: string;
  private readonly _memo: string;
  constructor(json: ReservationResponse) {
    this._id = json.reservation.id;
    this._studioId = json.reservation.studio_id;
    this._studioName = json.reservation.studio_name;
    this._startAt = new Date(json.reservation.start_at);
    this._finishAt = new Date(json.reservation.finish_at);
    this._maxUsageHour = json.reservation.max_usage_hour;
    this._memberId = json.reservation.member_id;
    this._memberName = json.reservation.member_name;
    this._memo = json.reservation.memo;
  }
  get id(): number {
    return this._id;
  }

  get studioId(): number {
    return this._studioId;
  }

  get studioName(): string {
    return this._studioName;
  }

  get startAt(): Date {
    return this._startAt;
  }

  get finishAt(): Date {
    return this._finishAt;
  }

  get maxUsageHour(): number {
    return this._maxUsageHour;
  }

  get memberId(): number {
    return this._memberId;
  }

  get memberName(): string {
    return this._memberName;
  }

  get memo(): string {
    return this._memo;
  }

  get startAtDateToJaLocale(): string {
    return this._startAt.toLocaleDateString('ja-JP',  {
      year:'numeric',
      month:'short',
      day:'numeric',
      weekday:'short'
    })
  }

  /**
   * x時x分の形式で取得
   */
  get startAtTimeToJaLocale(): string {
    return this._startAt.getHours().toString().padStart(2, '0') + '時' + this._startAt.getMinutes().toString().padStart(2, '0') + '分';
  }

  get usageHour(): number {
    // ミリ秒単位で扱われる。UNIXタイムで終了時間と開始時間の差を出して、それを1時間のミリ秒で割る。
    // 10:00:00 〜 12:59:59の利用を3時間と表現するので、1000ミリ秒足して計算する
    return Math.ceil((this._finishAt.getTime() - this._startAt.getTime() + 1000) / (1000 * 60 * 60));
  }
}
