export const padDateAndMonth = (date: string | number) => {
  return date.toString().padStart(2, '0');
};
