<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDevisStatusRequest;
use App\Models\Devis;
use App\Services\Admin\DevisService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class DevisController extends Controller
{
    public function __construct(
        private readonly DevisService $devisService
    ) {}

    /**
     * Liste des devis avec filtres et pagination
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'service' => $request->get('service'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];
        
        $devis = $this->devisService->getFilteredDevis($filters, 20);
        
        $services = Devis::distinct('service')
            ->pluck('service')
            ->filter()
            ->values();
        
        return view('admin.devis.index', [
            'devis' => $devis,
            'services' => $services,
            'filters' => $filters
        ]);
    }

    /**
     * Détail d'un devis
     */
    public function show(Devis $devis): View
    {
        $devis->load('contact');
        
        return view('admin.devis.show', [
            'devis' => $devis
        ]);
    }

    /**
     * Mise à jour du statut d'un devis
     */
    public function updateStatus(Request $request, Devis $devis): RedirectResponse
    {
        // Validation directe au lieu du FormRequest
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'note' => ['nullable', 'string', 'max:1000']
        ]);
        
        $this->devisService->updateStatus(
            $devis,
            $validated['status'],
            $request->user(),
            $validated['note'] ?? null
        );
        
        return redirect()
            ->route('admin.devis.show', $devis)
            ->with('success', 'Statut mis à jour avec succès');
    }

    /**
     * Mise à jour du montant
     */
    public function updateAmount(Request $request, Devis $devis): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        try {
            $devis->amount = $validated['amount'];
            $devis->save();

            Log::info('Montant devis mis à jour', [
                'devis_id' => $devis->id,
                'amount' => $validated['amount'],
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->route('admin.devis.show', $devis)
                ->with('success', 'Montant mis à jour avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour montant', [
                'devis_id' => $devis->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('admin.devis.show', $devis)
                ->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function destroy(Devis $devis): RedirectResponse
    {
        $this->devisService->deleteDevis($devis, auth()->user());
        
        return redirect()
            ->route('admin.devis.index')
            ->with('success', 'Devis supprimé avec succès');
    }

    public function generatePdf(Devis $devis)
    {
        $devis->load('contact');

        $data = [
            'devis' => $devis,
            'company' => [
                'name' => 'Biscuits Dev',
                'address' => '123 Rue du Code',
                'zip' => '75000 Paris',
                'email' => 'admin@biscuits.dev',
                'phone' => '+33 1 23 45 67 89',
                'siret' => '123 456 789 00012'
            ]
        ];

        $pdf = Pdf::loadView('admin.devis.pdf', $data);

        $pdf->setPaper('a4', 'portrait');

        // Nom du fichier : DEVIS-REF-CLIENT.pdf
        $filename = sprintf(
            'DEVIS-%s-%s.pdf', 
            $devis->reference ?? $devis->id, 
            now()->format('Ymd')
        );

        return $pdf->download($filename);
        return $pdf->stream($filename);
    }
}