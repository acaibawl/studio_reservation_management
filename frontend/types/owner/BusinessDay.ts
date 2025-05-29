export interface BusinessDay {
  regular_holidays: {
    code: number;
  }[];
  business_time: {
    open_time: string;
    close_time: string;
  };
}
