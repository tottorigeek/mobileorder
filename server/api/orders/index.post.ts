// 注文作成API（モック）
export default defineEventHandler(async (event) => {
  const body = await readBody(event)
  
  // TODO: データベースに保存
  const order = {
    id: Date.now().toString(),
    orderNumber: `ORD-${Date.now()}`,
    tableNumber: body.tableNumber,
    items: body.items,
    status: 'pending',
    totalAmount: body.totalAmount,
    createdAt: new Date(),
    updatedAt: new Date()
  }
  
  return order
})

