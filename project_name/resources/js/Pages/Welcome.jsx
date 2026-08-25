import { Head } from '@inertiajs/react';

export default function Welcome({ auth, laravelVersion, phpVersion }) {
   

    return (
        <>
            <Head title="Welcome" />
            <div>
                <h1>Welcome</h1>
                <p>You are logged in!</p>
            </div>
        </>
    );
}
