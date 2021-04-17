<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//require APPPATH . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Commission extends Admin_Controller {

    public function export($id)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Akio')
            ->setLastModifiedBy('Akio')
            ->setTitle('Lamoga commission')
            ->setSubject('commission  list')
            ->setDescription('Lamoga commission .');
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle("commission ");

        $worksheet->setCellValueByColumnAndRow(1, 1, 'Portal:');
        $worksheet->setCellValueByColumnAndRow(2, 1, 'LAMOGA');

        $wpDb = $this->load->database('lamoga', TRUE);
        $query = $wpDb->select('ID,mentor_id,user_login')->where('mentor_id >', '0')->from('pts_useradressen'.$this->wa_portal_id)->get();

        $resA=[];
        $row0=4;
        $col=0;
        $worksheet->setCellValueByColumnAndRow($col+1, $row0, 'ID');
        $worksheet->setCellValueByColumnAndRow($col+2, $row0, 'Portalname');
        $worksheet->setCellValueByColumnAndRow($col+3, $row0, 'Provision');
        $row0++;
        $worksheet->setCellValueByColumnAndRow($col+1, $row0, $id);
        $userQuery = $wpDb->select('user_login')->where('ID', $id)->from('pts_useradressen'.$this->wa_portal_id)->get();
        $worksheet->setCellValueByColumnAndRow($col+2, $row0, $userQuery->row()->user_login);

        $cnA=$row0+3;
        $worksheet->setCellValueByColumnAndRow(1, $cnA, 'Stufe A');
        $cnA++;

        $totalCommission=0;
        $worksheet->setCellValueByColumnAndRow($col+1, $cnA, 'ID');
        $worksheet->setCellValueByColumnAndRow($col+2, $cnA, 'Portalname');
        $worksheet->setCellValueByColumnAndRow($col+3, $cnA, 'Umsatz');
        $worksheet->setCellValueByColumnAndRow($col+4, $cnA, 'Provisonsanteil');
        $worksheet->setCellValueByColumnAndRow($col+5, $cnA, 'Provision');
        foreach ($query->result() as $row)
        {
            if ($id==$row->mentor_id){
                $cnA++;
                $resA[]=$row->ID;
                $salesQuery = $wpDb->select('SUM(gesamt_brutto) as sales')->where('berater_wp_uid', $row->ID)->from('pts_tbl_statistik_short'.$this->wa_portal_id)->get();
                $sales=$salesQuery->row()->sales;
                $commission=$sales*0.07;
                $totalCommission+=$commission;
                $worksheet->setCellValueByColumnAndRow($col+1, $cnA, $row->ID);
                $worksheet->setCellValueByColumnAndRow($col+2, $cnA, $row->user_login);
                $worksheet->setCellValueByColumnAndRow($col+3, $cnA, $sales.' €');
                $worksheet->setCellValueByColumnAndRow($col+4, $cnA, '7%');
                $worksheet->setCellValueByColumnAndRow($col+5, $cnA, $commission.' €');
            }
        }
        $cnA+=3;
        $worksheet->setCellValueByColumnAndRow(1, $cnA, 'Stufe B');
        $cnA++;
        $worksheet->setCellValueByColumnAndRow($col+1, $cnA, 'ID');
        $worksheet->setCellValueByColumnAndRow($col+2, $cnA, 'Portalname');
        $worksheet->setCellValueByColumnAndRow($col+3, $cnA, 'Umsatz');
        $worksheet->setCellValueByColumnAndRow($col+4, $cnA, 'Provisonsanteil');
        $worksheet->setCellValueByColumnAndRow($col+5, $cnA, 'Provision');
        $worksheet->setCellValueByColumnAndRow($col+6, $cnA, 'Stufe A Mentor ID');

        foreach ($resA as $idA)
        {
            foreach ($query->result() as $row) {
                if ($idA == $row->mentor_id) {
                    $cnA++;
                    $salesQuery = $wpDb->select('SUM(gesamt_brutto) as sales')->where('berater_wp_uid', $row->ID)->from('pts_tbl_statistik_short'.$this->wa_portal_id)->get();
                    $sales=$salesQuery->row()->sales;
                    $commission = $sales * 0.03;
                    $totalCommission+=$commission;
                    $worksheet->setCellValueByColumnAndRow($col + 1, $cnA, $row->ID);
                    $worksheet->setCellValueByColumnAndRow($col + 2, $cnA, $row->user_login);
                    $worksheet->setCellValueByColumnAndRow($col + 3, $cnA, $sales . ' €');
                    $worksheet->setCellValueByColumnAndRow($col + 4, $cnA, '3%');
                    $worksheet->setCellValueByColumnAndRow($col + 5, $cnA, $commission . ' €');
                    $worksheet->setCellValueByColumnAndRow($col+6, $cnA, $idA);
                }
            }
        }

        $worksheet->setCellValueByColumnAndRow($col+3, $row0, $totalCommission.' €');


//        $field_len = count(StudentModel::$fields) + 1;
//        $end_alpha = chr(63 + $field_len);
//        $id = 0;
//        foreach (StudentModel::$fields as $key => $value) {
//            $id++;
//            $worksheet->setCellValueByColumnAndRow($id, 1, $value['name']);
//            $worksheet->getColumnDimensionByColumn($id)->setWidth(isset($value['width'])?$value['width']:12);
//        }
//
//        // Set the first line style
//        //
//        $styleArray = [
//            'font' => [
//                'bold' => true
//            ],
////                    'alignment' => [
////                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
////                    ],
//        ];
//        $worksheet->getStyle('A1:' . $end_alpha . '1')->applyFromArray($styleArray); //->getFont()->setSize(14)

//        $students = Database::table("users")->where("role", "user")->get();
//        $len = count($students);
//        for ($i = 0; $i < $len; $i++) {
//            $j = $i + 2;
//            $id = 0;
//            $array = json_decode(json_encode($students[$i]), true);
//            foreach (StudentModel::$fields as $key => $value) {
//                $id++;
//                $worksheet->setCellValueByColumnAndRow($id, $j, $array[$key]);
//            }
//        }
        // Set the data table style
//            $styleArrayBody = [
//                'borders' => [
//                    'allBorders' => [
//                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
//                        'color' => ['argb' => '666666'],
//                    ],
//                ],
//                'alignment' => [
//                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
//                ],
//            ];
//
//            $total_rows = $len + 1;
//            //Add all borders/centered
//            $worksheet->getStyle('A1:' . $end_alpha . $total_rows)->applyFromArray($styleArrayBody);

        $filename = 'commission.xlsx';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $filename);
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }
}
