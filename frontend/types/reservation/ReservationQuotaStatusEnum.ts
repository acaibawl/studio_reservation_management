export const enum ReservationQuotaStatusEnum {
  NOT_AVAILABLE = 'not available',
  RESERVED = 'reserved',
  AVAILABLE = 'available',
}
export const reservationQuotaStatusEnumLabel = (status: ReservationQuotaStatusEnum): string => {
  switch (status) {
    case ReservationQuotaStatusEnum.NOT_AVAILABLE:
      return '×';
    case ReservationQuotaStatusEnum.RESERVED:
      return '有';
    case ReservationQuotaStatusEnum.AVAILABLE:
      return '⚪︎';
  }
};
