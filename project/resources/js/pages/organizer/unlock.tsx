import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { unlock } from '@/routes/organizer';

export default function Unlock() {
    return (
        <>
            <Head title="Organisatrice" />

            <Form {...unlock.form()} className="flex flex-col gap-6">
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="code">Code secret</Label>
                            <PasswordInput
                                id="code"
                                name="code"
                                required
                                autoFocus
                                autoComplete="off"
                            />
                            <InputError message={errors.code} />
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                        >
                            {processing && <Spinner />}
                            Voir les questions
                        </Button>
                    </>
                )}
            </Form>
        </>
    );
}

Unlock.layout = {
    title: 'Espace organisatrice',
    description: 'Entre ton code secret pour lire toutes les questions',
};
