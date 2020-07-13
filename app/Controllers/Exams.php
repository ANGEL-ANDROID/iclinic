<?php

namespace App\Controllers;

use DateTime;
use App\Models\ExamsModel;
use App\Models\PatientsModel;

class Exams extends BaseController
{
	public function index()
	{
		$model = new ExamsModel();
		$patients_model = new PatientsModel();

		$data = [
			'exams'  => $model->find(),
			'patients' => $patients_model->find(),
			'title' => 'Εξετάσεις',
		];

		echo view('templates/header', $data);
		echo view('exams/overview', $data);
		echo view('templates/footer', $data);
	}

	public function edit($id = null)
	{

		$model = new ExamsModel();
		$patients_model = new PatientsModel();

		$data = [
			'exam'  => $model->find($id),
			'patients'  => $patients_model->find(),
			'title' => 'Επεξεργασία Εξέτασης',
			'form_action' => '/patients/update',
			'is_new' => false
		];

		if (empty($data['exam'])) {
			throw new \CodeIgniter\Exceptions\PageNotFoundException('Δεν βρέθηκε η εξέταση με id: ' . $id);
		}

		$data['exam']['scheduled_date_iso8601'] = date('Y-m-d\TH:i', strtotime($data['exam']['scheduled_date']));

		if ($this->request->getVar('update_success')) {
			$data['messages'] =  "Επιτυχής ενημέρωση εξέτασης.";
		}


		echo view('templates/header', $data);
		echo view('exams/form', $data);
		echo view('templates/footer', $data);
	}

	/**
	 * Store records
	 */
	protected function store($is_new)
	{

		$model = new ExamsModel();
		$patients_model = new PatientsModel();

		$data = [
			'patients' => $patients_model->find(),
			'exam'  => [
				'id' => '',
				'patient_amka'  => $this->request->getVar('patient_amka'),
				'scheduled_date'  => $this->request->getVar('scheduled_date'),
				'status'  => $this->request->getVar('status'),
				'code'  => $this->request->getVar('code'),
			],
			'title' => 'Καταχώρηση εξέτασης',
			'is_new' => $is_new
		];

		$data['exam']['scheduled_date_iso8601'] = $data['exam']['scheduled_date'] ? date('Y-m-d\TH:i', strtotime($this->request->getVar('scheduled_date'))) : '';

		if ($this->request->getMethod() == 'post') {

			if (!$this->validate(
				[
					'patient_amka'  => 'required',
					'scheduled_date'  => 'required',
					'status'  => 'required',
					'code'  => 'required',
				],
				[
					'patient_amka'  => ['required' => 'Επιλέξτε ασθενή'],
					'scheduled_date'  => ['required' => 'Συμπληρώστε την ημερομηνία'],
					'status'  => ['required' => 'Συμπληρώστε την κατάσταση'],
					'code'  => ['required' => 'Επιλέξτε εξέταση'],
				]
			)) {
			} else {
				$id = $this->request->getVar('id');
				if ($is_new) {
					$id = $model->insert([
						'patient_amka'  => $this->request->getVar('patient_amka'),
						'scheduled_date'  => $this->request->getVar('scheduled_date'),
						'status'  => $this->request->getVar('status'),
						'code'  => $this->request->getVar('code'),
					], true);
				} else {
					$model->update($id, [
						'patient_amka'  => $this->request->getVar('patient_amka'),
						'scheduled_date'  => $this->request->getVar('scheduled_date'),
						'status'  => $this->request->getVar('status'),
						'code'  => $this->request->getVar('code'),
					]);
				}

				return redirect()->to('/exams/' . $id . '?update_success=1');
			}
		}

		echo view('templates/header', $data);
		echo view('exams/form', $data);
		echo view('templates/footer', $data);
	}
}
