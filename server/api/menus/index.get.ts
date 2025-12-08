// メニュー一覧取得API（モック）
export default defineEventHandler(async (event) => {
  // TODO: データベースから取得
  return [
    {
      id: '1',
      number: '001',
      name: 'ハンバーガー',
      description: 'ジューシーなハンバーガー',
      price: 800,
      category: 'food',
      isAvailable: true,
      isRecommended: true
    },
    {
      id: '2',
      number: '002',
      name: 'フライドポテト',
      description: 'カリッと揚げたポテト',
      price: 400,
      category: 'food',
      isAvailable: true
    },
    {
      id: '3',
      number: '003',
      name: 'コーラ',
      description: '冷たいコーラ',
      price: 300,
      category: 'drink',
      isAvailable: true
    },
    {
      id: '4',
      number: '004',
      name: 'オレンジジュース',
      description: 'フレッシュなオレンジジュース',
      price: 350,
      category: 'drink',
      isAvailable: true
    },
    {
      id: '5',
      number: '005',
      name: 'チーズバーガー',
      description: 'チーズたっぷりのハンバーガー',
      price: 900,
      category: 'food',
      isAvailable: true,
      isRecommended: true
    },
    {
      id: '6',
      number: '006',
      name: 'アイスクリーム',
      description: 'バニラアイスクリーム',
      price: 400,
      category: 'dessert',
      isAvailable: true
    }
  ]
})

