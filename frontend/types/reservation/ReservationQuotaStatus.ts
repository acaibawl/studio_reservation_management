export const ReservationQuotaStatus = {
  NOT_AVAILABLE: 'not available',
  RESERVED: 'reserved',
  AVAILABLE: 'available',
};

export type ReservationQuotaStatus = typeof ReservationQuotaStatus[keyof typeof ReservationQuotaStatus];
export const reservationQuotaStatusLabel = (status: ReservationQuotaStatus): string => {
  switch (status) {
    case ReservationQuotaStatus.NOT_AVAILABLE:
      return '×';
    case ReservationQuotaStatus.RESERVED:
      return '有';
    case ReservationQuotaStatus.AVAILABLE:
      return '⚪︎';
  }
  throw new Error('Invalid status');
};
