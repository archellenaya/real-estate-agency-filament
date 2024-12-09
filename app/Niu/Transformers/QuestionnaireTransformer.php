<?php
/**
 * Created by PhpStorm.
 * User: omar
 * Date: 13/10/2015
 * Time: 11:48
 */
    namespace App\Niu\Transformers;

    class QuestionnaireTransformer extends Transformer {

        public function transform( $questionnaire ) {
            return [
                'buyer_ref'     => $questionnaire['buyer_ref'],
                'title'         => $questionnaire['title'],
                'first_name'    => $questionnaire['first_name'],
                'last_name'     => $questionnaire['last_name'],
                'address_1'     => $questionnaire['address_1'],
                'address_2'     => $questionnaire['address_2'],
                'post_code'     => $questionnaire['post_code'],
                'country'       => $questionnaire['country'],
                'tel_1'         => $questionnaire['tel_1'],
                'tel_2'         => $questionnaire['tel_2'],
                'fax'           => $questionnaire['fax'],
                'email'         => $questionnaire['email'],
                'consultants'   => $questionnaire['consultants'],
                'inspection_date'=> $questionnaire['inspection_date']
            ];
        }
    }