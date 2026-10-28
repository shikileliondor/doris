import { Head, usePoll } from '@inertiajs/react';

type Question = {
    id: number;
    body: string;
    created_at: string;
};

type Props = {
    questions: Question[];
};

const when = new Intl.DateTimeFormat('fr-FR', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
});

export default function Questions({ questions }: Props) {
    // Questions arrive at any time: refresh the list in the background.
    usePoll(10000, { only: ['questions'] });

    return (
        <>
            <Head title="Toutes les questions" />
            <main className="mx-auto flex min-h-svh w-full max-w-2xl flex-col gap-6 bg-background px-4 py-8">
                <header className="space-y-1">
                    <h1 className="text-2xl font-semibold">
                        Toutes les questions
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {questions.length === 0
                            ? 'Aucune question pour le moment.'
                            : `${questions.length} question${questions.length > 1 ? 's' : ''}, la plus récente en premier. La liste se met à jour toute seule.`}
                    </p>
                </header>

                <ul className="flex flex-col gap-3">
                    {questions.map((question) => (
                        <li
                            key={question.id}
                            className="rounded-xl border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                        >
                            <p className="break-words whitespace-pre-wrap">
                                {question.body}
                            </p>
                            <time
                                dateTime={question.created_at}
                                className="mt-2 block text-xs text-muted-foreground"
                            >
                                {when.format(new Date(question.created_at))}
                            </time>
                        </li>
                    ))}
                </ul>
            </main>
        </>
    );
}
