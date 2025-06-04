import {ReservationQuotaStatusEnum} from "~/types/reservation/ReservationQuotaStatusEnum";

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
  };
}

export interface ReservationQuotasResponse {
  date: string;
  studios: Studio[];
}

export interface ReservationQuotaNotReserved {
  hour: number;
  status: ReservationQuotaStatusEnum.NOT_AVAILABLE | ReservationQuotaStatusEnum.AVAILABLE;
}

export interface ReservationQuotaReserved {
  hour: number;
  status: ReservationQuotaStatusEnum.RESERVED;
  reservation_id: number;
}

export interface Studio {
  id: number;
  name: string;
  start_at: number;
  reservation_quotas: (ReservationQuotaNotReserved | ReservationQuotaReserved)[];
}

export class Reservation {
  private readonly _startAt: Date;
  private readonly _finishAt: Date;

  constructor(
    public readonly id: number,
    public readonly studioId: number,
    public readonly studioName: string,
    _startAt: string,
    _finishAt: string,
    public readonly maxUsageHour: number | undefined = undefined,
    public readonly memberId: number,
    public readonly memberName: string = '',
    public readonly memo: string = '',
  ) {
    this._startAt = new Date(_startAt);
    this._finishAt = new Date(_finishAt);
  }

  get startAt(): Date {
    return this._startAt;
  }

  get finishAt(): Date {
    return this._finishAt;
  }

  get startAtDateToJaLocale(): string {
    return this._startAt.toLocaleDateString('ja-JP', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      weekday: 'short',
    });
  }

  get startAtDateToYYYYMMDDKebab(): string {
    // sv-SEロケールはYYYY-MM-DD形式の日付文字列を戻す
    return this._startAt.toLocaleDateString('sv-SE', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
    });
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
