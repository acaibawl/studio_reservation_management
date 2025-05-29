export const weekDays = ['日', '月', '火', '水', '木', '金', '土'];
/**
 * 指定された日付文字列に対応する曜日を取得します。
 * @param date - 日付文字列 (例: "2023-10-01")
 * @returns 曜日 (例: "日"), 無効な日付の場合には"Invalid Date"を返します。
 */
export const getWeekDay = (date: string): string => {
  const parsedDate = new Date(date);

  if (isNaN(parsedDate.getTime())) {
    // 無効な日付の場合はエラーを防ぐ
    return 'Invalid Date';
  }

  return weekDays[parsedDate.getDay()];
};
