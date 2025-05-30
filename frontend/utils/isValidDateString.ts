export const isValidDateString = (dateString: string) => {
  // -で3ブロックに区切られていない場合はfalse
  if (dateString.split('-').length !== 3) {
    return false;
  }
  const timestamp = Date.parse(dateString);
  return !isNaN(timestamp);
};
