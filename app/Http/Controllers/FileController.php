<?php 

namespace App\Http\Controllers;
/**
 * API5
 */
use DateTime;
use App\File; 
use Exception;
use Illuminate\Http\Response;
use App\Models\Property;
use Illuminate\Support\Facades\Input;
use Intervention\Image\Facades\Image;
use App\Http\Requests\StoreFileRequest;
use Illuminate\Support\Facades\Storage;
use App\Niu\Transformers\FileTransformer;

/**
 * Class FileController
 *
 * @package App\Http\Controllers
 */
class FileController extends ApiController {
	/**
	 * @var FileTransformer
	 */
	protected $fileTransformer;


	const WATERMARK = 'watermark';
	const THUMBNAIL = 'thumbnail';
	const WHITELABEL = 'whitelabel';
	const WEBFULL = 'webfull';
	const WATERMARK_2 = 'watermark_2';
	const THUMBNAIL_2 = 'thumbnail_2';
	const THUMBNAIL_REBRAND = 'thumbnail_rebrand';
	const WHITELABEL_2 = 'whitelabel_2';
	const WEBFULL_2 = 'webfull_2';
	const WEBFULL_REBRAND = 'webfull_rebrand';

	/**
	 * level of protection
	 *
	 * @var array
	 */
	protected $apiMethods = [
		'index'            => [
			'level' => 10
		],
		'show'             => [
			'level' => 10
		],
		'store'            => [
			'level' => 20
		],
		'update'           => [
			'level' => 20
		],
		'destroy'          => [
			'level' => 20
		],
		'getPropertyImage' => [
			'keyAuthentication' => false
		],
		'getPropertyPDF'   => [
			'keyAuthentication' => false
		]
	];

	/**
	 * FileController constructor.
	 *
	 * @param FileTransformer $fileTransformer
	 */
	public function __construct( FileTransformer $fileTransformer ) {
		// parent::__construct();
		$this->fileTransformer = $fileTransformer;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param null $propertyId
	 *
	 * @return Response
	 */
	public function index( $propertyId = null ) {
		$files = $this->getFiles( $propertyId );

		return $this->respond(
			[
				'data' => $this->fileTransformer->transformCollection( $files->all() )
			]
		);
	}

	public function getFile( $id ) {
		$file = File::findOrFail( $id );

		$filename = 'uploads/' . $file->file_name_field;
		$img      = Image::make( $filename );

		//		$watermark = Image::make( 'watermark.png' )->resize(500, null, function ($constraint) {
		//			$constraint->aspectRatio();
		//		});
		$watermark = Image::make( 'watermark-new.png' );
		$img->insert( $watermark, 'center' );

		$img->encode( 'data-url' );

		return $img;
		//		return '<img src="' . $img . '">';

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param StoreFileRequest $request
	 *
	 * @return Response
	 */
	public function store( StoreFileRequest $request ) {
		try {
			$file = $request->file( 'file' );

			// required to compute name or save image
			$timestamp        = time();
			$extension        = $file->getClientOriginalExtension();
			$original_file_name = $file->getClientOriginalName();

			// fields to be saved to database
			$propertyRef = Input::get( 'propertyWebRefField' );
			$fileType    = Input::get( 'file_type_field' );
			$seqNo       = Input::get( 'sequence_no_field' );
			$seoText     = Input::get( 'seo_url_field' );
			$mime_type   = $file->getMimeType();
			$image_name  = $propertyRef . '_' . $seqNo . '_' . $timestamp . '.' . $extension;

			// before creating entry in database so that if an error is thrown the entry will not be saved
			//			$file->move( 'properties', $image_name );
			$s3 = Storage::disk( 's3' );
			$s3->put( 'properties/' . $image_name, file_get_contents( $file ) );

			$file = File::create(
				[
					'property_id'      => $propertyRef,
					'file_name_field'    => $image_name,
					'file_type_field'    => $fileType,
					'sequence_no_field'  => $seqNo,
					'mime'             => $mime_type,
					'original_file_name' => $original_file_name,
					'seo_url_field'      => $seoText,
				]
			);

			return $this->respond( $file->toArray() );
		} catch ( Exception $e ) {
			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function destroy( $id ) {
		try {
			$file = File::findOrFail( $id );
			$file->delete();

			return $this->respond(
				[
					'message' => 'Successfully Deleted',
					'data'    => $this->fileTransformer->transform( $file )
				]
			);
		} catch ( Exception $e ) {
			return $this->setStatusCode( Response::HTTP_BAD_REQUEST )->respondWithError( $e->getMessage() );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function destroyAll( $id ) {
		$file         = File::where( 'property_id', '=', $id );
		$file_details = $file->get();

		if ( $file_details->count() > 0 ) {
			$file->delete();

			return $this->respond( [
				'message'       => 'Files deleted successfully',
				'data'          => $file_details->toArray(),
				'items_deleted' => $file_details->count()
			] );
		} else {
			return $this->respondWithError( 'No Files Found' );
		}
	}

	/**
	 * @param $propertyID
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function getFiles( $propertyID ) {
		$files = $propertyID ? Property::findOrFail( $propertyID )->files : File::all();

		return $files;
	}

	/**
	 * @param String $filename
	 * @param String $type
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getPropertyImage( $type, $filename ) {
		$whiteList_type = [
			self::WATERMARK,
			self::THUMBNAIL,
			self::WHITELABEL,
			self::WEBFULL,
			self::WATERMARK_2,
			self::THUMBNAIL_2,
			self::THUMBNAIL_REBRAND,
			self::WHITELABEL_2,
			self::WEBFULL_2,
			self::WEBFULL_REBRAND,
		];
		if ( ! in_array( $type, $whiteList_type ) ) {
			return $this->respondWithError( 'Type does not exist' );
		}

		$s3   = Storage::disk( 's3' );
		$path = 'properties-' . $type . '/';
		$this->create_image( $s3, $filename, $type, $path );
		$image        = $s3->get( $path . $filename );
		$dataTime     = new DateTime();
		$lastModified = $dataTime->setTimestamp( $s3->lastModified( $path . $filename ) );
		$response     = \Response::make( $image, 200, [ 'Content-Type' => 'image/jpeg' ] );
		$response->setCache(
			[
				'last_modified' => $lastModified,
				'max_age'       => 2628000000, // One month
				'public'        => true,   // Allow public caches to cache
			]
		);

		return $response;

	}

	/**
	 * @param String $filename
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getPropertyPDF( $filename ) {
		$s3       = Storage::disk( 's3' );
		$path     = 'properties/';
		$pdf      = $s3->get( $path . $filename );
		$response = \Response::make(
			$pdf,
			200,
			[
				'Content-Type' => 'application/pdf'
			]
		);
		$response->setCache(
			[
				'max_age' => 2628000000, // One month
				'public'  => true,   // Allow public caches to cache
			]
		);

		return $response;
	}

	/**
	 * @param  \Illuminate\Contracts\Filesystem\Filesystem $s3
	 * @param  String                                      $filename
	 * @param  String                                      $type
	 */
	private function create_image( $s3, $filename, $type, $path ) {

		if ( ! $s3->exists( $path . $filename ) ) {
			$file = $s3->get( 'properties/' . $filename );

			$image = Image::make( $file );
			if ( $type == self::WHITELABEL || $type == self::WHITELABEL_2 ) {
				$watermark = Image::make( 'watermark-whitelabel.png' );
			} else {
				$watermark = Image::make( 'watermark-new.png' );
			}
			$image->insert( $watermark, 'center' );

			$full_size = [
				self::WEBFULL,
				self::WEBFULL_2,
				self::WEBFULL_REBRAND,
				self::WHITELABEL,
				self::WHITELABEL_2
			];
			if ( $type === self::THUMBNAIL || $type == self::THUMBNAIL_2 || $type == self::THUMBNAIL_REBRAND ) {
				$image->resize( 450, null, function ( $constraint ) {
					$constraint->aspectRatio();
				} );
			} elseif ( in_array( $type, $full_size ) ) {
				$image->resize( 1024, null, function ( $constraint ) {
					$constraint->aspectRatio();
				} );
			}

			$s3->put( $path . $filename, (string) $image->encode( null, 60 ) );
		}
	}
}