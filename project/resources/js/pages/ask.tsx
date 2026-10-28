import { Head, useHttp } from '@inertiajs/react';
import { AskBar } from '@/components/ui/ask-bar';
import type { AskModel } from '@/components/ui/ask-bar';
import { store } from '@/routes/questions';

const MODES: AskModel[] = [
    {
        id: 'anonyme',
        name: 'Anonyme',
        detail: "Personne ne saura que c'est toi, même pas l'organisatrice",
    },
];

export default function Ask() {
    const http = useHttp({ body: '' });

    // A validation error resolves with nothing; anything else that fails throws.
    const respond = async (prompt: string) => {
        http.transform(() => ({ body: prompt }));
        const sent = await http.post(store.url()).catch(() => null);

        return sent
            ? "C'est envoyé 💌 Ta question est partie sans ton nom. Tu peux en poser une autre quand tu veux."
            : "Oups, ta question n'est pas partie. Attends un petit moment et réessaie.";
    };

    return (
        <>
            <Head title="Pose ta question" />
            <AskBar
                className="w-full"
                greeting="Pose ta question, personne ne saura que c'est toi"
                placeholder="Écris ta question…"
                models={MODES}
                respond={respond}
                attachments={false}
                theme="dark"
            />
        </>
    );
}
