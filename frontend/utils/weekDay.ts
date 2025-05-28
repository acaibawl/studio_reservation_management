export const weekDay = ["日", "月", "火", "水", "木", "金", "土"];
export const getWeekDay = (date: string) => weekDay[new Date(date).getDay()];