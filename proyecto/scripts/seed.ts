import { createClient } from '@supabase/supabase-js'

const supabaseUrl = 'http://127.0.0.1:54321'
const supabaseAnonKey = 'sb_publishable_ACJWlzQHlZjBrEguHvfOxg_3BJgxAaH'

const supabase = createClient(supabaseUrl, supabaseAnonKey)

async function seed() {
  // Create admin user
  const { data: admin, error: adminError } = await supabase.auth.signUp({
    email: 'admin@admilico.com',
    password: 'admin123',
    options: {
      data: { full_name: 'Admin AdmiLico', role: 'admin' },
    },
  })

  if (adminError) {
    console.error('Error creating admin:', adminError.message)
  } else {
    console.log('Admin user created:', admin.user?.email)
  }

  // Create cashier user
  const { data: cashier, error: cashierError } = await supabase.auth.signUp({
    email: 'cajero@admilico.com',
    password: 'cajero123',
    options: {
      data: { full_name: 'Cajero Principal', role: 'cashier' },
    },
  })

  if (cashierError) {
    console.error('Error creating cashier:', cashierError.message)
  } else {
    console.log('Cashier user created:', cashier.user?.email)
  }

  // Create stock manager user
  const { data: stock, error: stockError } = await supabase.auth.signUp({
    email: 'almacen@admilico.com',
    password: 'almacen123',
    options: {
      data: { full_name: 'Encargado Almacén', role: 'stock_manager' },
    },
  })

  if (stockError) {
    console.error('Error creating stock manager:', stockError.message)
  } else {
    console.log('Stock manager user created:', stock.user?.email)
  }

  // Update profiles to set correct roles (since trigger might not work with local dev)
  const { error: updateError } = await supabase
    .from('profiles')
    .update({ role: 'admin' })
    .eq('email', 'admin@admilico.com')

  if (updateError) {
    console.error('Error updating admin role:', updateError.message)
  }

  const { error: updateCashierError } = await supabase
    .from('profiles')
    .update({ role: 'cashier' })
    .eq('email', 'cajero@admilico.com')

  if (updateCashierError) {
    console.error('Error updating cashier role:', updateCashierError.message)
  }

  const { error: updateStockError } = await supabase
    .from('profiles')
    .update({ role: 'stock_manager' })
    .eq('email', 'almacen@admilico.com')

  if (updateStockError) {
    console.error('Error updating stock role:', updateStockError.message)
  }

  console.log('\n--- Seed complete ---')
  console.log('Admin: admin@admilico.com / admin123')
  console.log('Cashier: cajero@admilico.com / cajero123')
  console.log('Stock: almacen@admilico.com / almacen123')
}

seed()
