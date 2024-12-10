<?php namespace App\Http\Controllers;
/**
 * API5
 */
use Mail;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionnaireRequest;
use App\Niu\Transformers\QuestionnaireTransformer;
use App\Questionnaire;
use Exception;
use Illuminate\Http\Response;

/**
 * Class ConsultantController
 * @package App\Http\Controllers
 */
class QuestionnaireController extends ApiController {

    /**
     * @var $questionnaireTransformer QuestionnaireTransformer
     */
    private $questionnaireTransformer;

    /**
     * level of protection
     *
     * @var array
     */
    protected $apiMethods = [
        'show' => [
            'level' => 10
        ],
        'store' => [
            'level' => 20
        ],
    ];

    /**
     * Store a newly created questionnaire in storage.
     *
     * @param QuestionnaireRequest $request
     *
     * @return Response
     */
    public function store( QuestionnaireRequest $request ) {
        try {
            $insp_date = $request->input( 'inspection_date' );
            $insp_date = explode( '/', $insp_date );
            $insp_date = $insp_date[2] . '-' . $insp_date[1] . '-' . $insp_date[0];
            $questionnaire = Questionnaire::create(
                [
					'buyer_ref'    => $request->input( 'buyer_ref' ),
					'first_name'   => $request->input( 'first_name' ),
					'last_name'    => $request->input( 'last_name' ),
					'address_1'    => $request->input( 'address_1' ),
					'address_2'    => $request->input( 'address_2' ),
					'post_code'    => $request->input( 'post_code' ),
					'country'       => $request->input( 'country' ),
					'tel_1'         => $request->input( 'tel_1' ),
					'tel_2'         => $request->input( 'tel_2' ),
					'fax'           => $request->input( 'fax' ),
					'email'         => $request->input( 'email' ),
					'consultants'   => $request->input( 'consultants' ),
					'inspection_date'=> $insp_date,
                ]
            );

            $this->send_questionnaire_email( $questionnaire );

            $this->send_admin_questionnaire_email( $questionnaire );

            return $this->respond( $questionnaire );
        } catch ( Exception $e ) {
            return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show( $id ) {
        $questionnaire = Questionnaire::find( $id );

        if ( ! $questionnaire ) {
            return $this->respondNotFound( 'Questionnaire does not exist' );
        }

        return $this->respond(
            [
                'data' => $questionnaire
            ]
        );
    }

    /**
     * Send E-mail to client in order to fill out the post inspection questionnaire
     *
     * @param $data object
     *
     */
    public function send_questionnaire_email( $data ){

//        $info['title'] = $data->title;
        $info['first_name'] = $data->first_name;
        $info['last_name'] = $data->last_name;
        $info['email'] = explode( ';', $data->email );
        foreach( $info['email'] as &$email ){
            $email = trim( $email );
        }
        $info['id'] = $data->id;
        Mail::queue('emails.questionnaire', $info, function($message) use ( $info ) {
            $message->to( $info['email'], 'Frank Salt Ltd.')->subject('Post Inspection Questionnaire');
        });

    }

    /**
     * Send E-mail to admin in order to inform them of new Post Questionnaire
     *
     * @param $data object
     *
     */
    public function send_admin_questionnaire_email( $data ){

        $info['first_name'] = $data->first_name;
        $info['last_name'] = $data->last_name;
        $info['email'] = $data->email;
        $info['buyer'] = $data->buyer_ref;
        $info['id'] = $data->id;
        Mail::queue('emails.admin_questionnaire', $info, function($message) use ( $info ) {
            $message->to( 'mbartoli@franksalt.com.mt', 'Frank Salt Ltd.')->subject('Post Inspection Questionnaire');
        });

    }

}
