export const formatTimeToHi = (time: string | undefined) => {
  if (!time) {
    return '';
  }
  return time.split(':')[0] + ':' + time.split(':')[1].slice(0, 2);
};
