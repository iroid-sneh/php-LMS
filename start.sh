#!/bin/bash

echo "Starting PHP Leave Management System..."
echo

echo "Starting PHP development server for backend..."
cd php-LMS/backend
php -S localhost:8000 &
BACKEND_PID=$!

sleep 2

echo "Starting PHP development server for frontend..."
cd ../frontend
php -S localhost:3000 &
FRONTEND_PID=$!

echo
echo "Servers started successfully!"
echo
echo "Backend API: http://localhost:8000"
echo "Frontend: http://localhost:3000"
echo
echo "Default credentials:"
echo "HR Admin: admin@company.com / password"
echo "Employee: john@company.com / password"
echo
echo "Press Ctrl+C to stop servers..."

# Function to cleanup on exit
cleanup() {
    echo
    echo "Stopping servers..."
    kill $BACKEND_PID 2>/dev/null
    kill $FRONTEND_PID 2>/dev/null
    echo "Servers stopped."
    exit 0
}

# Set trap to cleanup on script exit
trap cleanup SIGINT SIGTERM

# Wait for user to stop
wait
