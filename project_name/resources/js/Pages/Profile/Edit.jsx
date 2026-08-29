import React from 'react';
import { Head, Link } from '@inertiajs/react';
import MobileLayout from '../../Layouts/MobileLayout';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import { Card, CardContent } from '../../Components/ui/card';
import { ArrowLeft, LogOut, User as UserIcon } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export default function Edit({ auth, mustVerifyEmail, status }) {
    const { t } = useTranslation();

    return (
        <MobileLayout>
            <Head title="Profile" />

            <div className="mb-6 space-y-4">
                {/* 🌟 Navigation Header */}
                <div className="flex items-center gap-3">
                    <Link href={route('dashboard')} className="p-2 bg-gray-200 rounded-full hover:bg-gray-300 transition">
                        <ArrowLeft size={20} />
                    </Link>
                    <div>
                        <h2 className="font-bold text-xl leading-tight">My Profile</h2>
                        <p className="text-sm text-gray-500">{auth.user.email}</p>
                    </div>
                </div>
            </div>

            <div className="space-y-6 pb-12">
                
                {/* 🌟 Profile Information Card */}
                <Card className="border-gray-200 shadow-sm overflow-hidden">
                    <div className="bg-blue-50 px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                        <UserIcon size={18} className="text-blue-600" />
                        <h3 className="font-semibold text-blue-900">Personal Information</h3>
                    </div>
                    <CardContent className="p-4 sm:p-6">
                        <UpdateProfileInformationForm
                            mustVerifyEmail={mustVerifyEmail}
                            status={status}
                            className="max-w-xl"
                        />
                    </CardContent>
                </Card>

                {/* 🌟 Update Password Card */}
                <Card className="border-gray-200 shadow-sm overflow-hidden">
                    <div className="bg-gray-50 px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                        <Lock size={18} className="text-gray-600" />
                        <h3 className="font-semibold text-gray-800">Update Password</h3>
                    </div>
                    <CardContent className="p-4 sm:p-6">
                        <UpdatePasswordForm className="max-w-xl" />
                    </CardContent>
                </Card>

                {/* 🌟 Secure Logout Button */}
                <div className="pt-4">
                    <Link 
                        href={route('logout')} 
                        method="post" 
                        as="button"
                        className="w-full flex items-center justify-center gap-2 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 font-bold py-4 rounded-xl transition-colors active:scale-[0.98]"
                    >
                        <LogOut size={20} />
                        Log Out
                    </Link>
                </div>

            </div>
        </MobileLayout>
    );
}

// Ensure you import Lock for the UI above
import { Lock } from 'lucide-react';