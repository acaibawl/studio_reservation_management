export const isValidDateString = (dateString: string) => {
  // YYYY-MM-DD の基本的な形式チェック
  if (!/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
    return false;
  }

  const date = new Date(dateString);

  // Dateオブジェクトが有効か、かつ、生成された日付文字列が元の入力と一致するかを確認
  // これにより "2023-02-30" のような不正な日付が "2023-03-02" などに解釈されるのを防ぐ
  return !isNaN(date.getTime())
    && date.toISOString().slice(0, 10) === dateString;
};
