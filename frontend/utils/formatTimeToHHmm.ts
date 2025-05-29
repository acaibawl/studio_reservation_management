export const formatTimeToHHmm = (time: string | undefined) => {
  if (!time) {
    return '';
  }
  const parts = time.split(':');
  if (parts.length < 2 || !parts[1]) {
    // 期待される形式でない場合は空文字を返すか、エラー処理を行う
    throw new Error('Invalid time format');
  }
  return parts[0] + ':' + parts[1].slice(0, 2);
};
