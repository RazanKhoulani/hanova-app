<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use App\Http\Controllers\Admin\FaqController;
use App\Models\Appointment;
use App\Models\Product;
use App\Models\Order;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'delivery']);

        // 2. Admin User
        $admin = User::firstOrCreate([
            'phone' => '+1234567890',
        ], [
            'name' => 'System Admin',
            'password' => bcrypt('password123'),
            'phone_verified_at' => now(),
        ]);
        $admin->phone_verified_at = now();
        if (Schema::hasColumn('users', 'availability_schedule')) {
            $admin->availability_schedule = [
                'duration_minutes' => [
                    'session' => 60,
                    'treatment' => 30,
                ],
                'clinic' => [
                    'default' => [
                        ['start' => '09:00', 'end' => '12:00'],
                        ['start' => '14:00', 'end' => '17:00'],
                    ],
                ],
                'online' => [
                    'default' => [
                        ['start' => '10:00', 'end' => '13:00'],
                        ['start' => '15:00', 'end' => '18:00'],
                    ],
                ],
            ];
        }
        $admin->save();
        $admin->assignRole($adminRole);

        $this->call(DeliverySetupSeeder::class);
        $this->call(ConcernSeeder::class);
        $this->call(OfferSeeder::class);

        // 3. Products (via ProductSeeder)
        $this->call(ProductSeeder::class);
        $this->call(ProductConcernSeeder::class);
        $products = Product::all();

        // 4. Test User & Patient
        $testUser = User::create([
            'name' => 'Ahmad Khalid',
            'phone' => '+963911111111',
            'password' => bcrypt('password123'),
            'phone_verified_at' => now(),
        ]);
        $testUser->assignRole($userRole);

        $patient = Patient::create([
            'user_id' => $testUser->id,
            'name' => 'Ahmad Khalid',
            'age' => 28,
            'phone' => '+963911111111',
            'address' => 'Damascus, Syria',
            'notes' => 'Patient suffers from chronic acne and is seeking treatment progress tracking.',
        ]);

        // 5. Appointments
        $firstAppointment = [
            'patient_id' => $patient->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '10:30',
            'type' => 'clinic',
            'appointment_type' => 'treatment',
            'duration_minutes' => 30,
            'status' => 'confirmed',
        ];
        if (Schema::hasColumn('appointments', 'doctor_id')) {
            $firstAppointment['doctor_id'] = $admin->id;
        }
        Appointment::create($firstAppointment);

        $secondAppointment = [
            'patient_id' => $patient->id,
            'date' => now()->addDays(5)->format('Y-m-d'),
            'time' => '14:00',
            'type' => 'online',
            'appointment_type' => 'session',
            'duration_minutes' => 60,
            'status' => 'pending',
        ];
        if (Schema::hasColumn('appointments', 'doctor_id')) {
            $secondAppointment['doctor_id'] = $admin->id;
        }
        Appointment::create($secondAppointment);

        // 6. Orders
        $order = Order::create([
            'user_id' => $testUser->id,
            'total_amount' => 150.00,
            'status' => 'paid',
            'payment_method' => 'card',
            'shipping_address' => 'Main St, Damascus',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $products->random()->id,
            'quantity' => 2,
            'price' => 75.00,
        ]);

        // 7. Consultations
        Consultation::create([
            'user_id' => $testUser->id,
            'doctor_id' => $admin->id,
            'type' => 'chat',
            'status' => 'active',
            'notes' => 'Initial consultation via chat. Patient discussed acne issues.',
        ]);

        // 8. Dummy Chat Conversations
        $conversation = Conversation::create([
            'user_id' => $testUser->id,
            'doctor_id' => $admin->id,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $testUser->id,
            'body' => 'Hello Doctor, I have a question about my treatment.',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'body' => 'Sure Ahmad, how can I help you today?',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $testUser->id,
            'body' => 'Should I continue with the current dosage?',
        ]);

        // 9. Dummy Notifications
        Notification::create([
            'title' => 'Welcome to Hanova',
            'body' => 'Thank you for choosing Hanova for your beauty care.',
        ]); // Broadcast

        Notification::create([
            'user_id' => $testUser->id,
            'title' => 'Appointment Confirmed',
            'body' => 'Your appointment for tomorrow is confirmed.',
        ]);

        // 10. FAQs
        $this->call(FaqSeeder::class);
    }
}
