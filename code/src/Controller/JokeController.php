<?php
namespace App\Controller;


use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Joke;
use App\Form\Type\JokeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JokeController
 * @package App\Controller
 */
class JokeController extends AbstractApiController
{
    /**
     * Returns multiple jokes
     *
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="The page number (Default page is 1)"
     * )
     * @OA\Parameter(
     *     name="perPage",
     *     in="query",
     *     description="Jokes per page (Default Jokes per page is 5)"
     * )
     * @OA\Parameter(
     *     name="qty",
     *     in="query",
     *     description="Max number of Jokes to return (Default is to return all Jokes)"
     * )
     * @OA\Parameter(
     *     name="random",
     *     in="query",
     *     description="Returns one random Joke"
     * )
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Filters Jokes based on string given"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns multiple jokes",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             @OA\Property(
     *                 property="jokes",
     *                 type="array",
     *                 @OA\Items(ref=@Model(type=Joke::class))
     *             ),
     *             @OA\Property(property="page", type="integer"),
     *             @OA\Property(property="jokesPerage", type="integer"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="filter", type="string", description="optional"),
     *         )
     *     )
     * )
     */
    public function index(Request $request): Response
    {
        $filter = $request->get('filter');
        if (!is_null($filter)) {
            $f = '%' . $filter . '%';
            $entityManager = $this->getDoctrine()->getManager();
            $query = $entityManager->createQuery(
                'SELECT j
                FROM App\Entity\Joke j
                WHERE j.punchline like :filter'
            )->setParameter('filter', $f);
            $jokes = $query->getResult();
        } else {
            $jokes = $this->getDoctrine()->getRepository(Joke::class)->findAll();
        }

        if (filter_var($request->get('random'), FILTER_VALIDATE_BOOLEAN)) {
            $jokes = $jokes[rand(0, count($jokes) - 1)];
        }

        $page = 0;
        if ($request->get('page')) {
            $page = (int) $request->get('page') - 1;
        }

        $perPage = 5;
        if ($request->get('perPage')) {
            $perPage = (int) $request->get('perPage');
        }

        $offset = $page * $perPage;

        $qty = $total = is_array($jokes) ? count($jokes) : 1;
        if ($request->get('qty')) {
            $qty = (int) $request->get('qty');
            if ($qty > $total) {
                $qty = $total;
            } else if ($qty < 1) {
                $qty = 1;
            }
        }
        if ($total > 1) {
            $jokes = array_slice($jokes, 0, $qty);
            $jokes = array_slice($jokes, $offset, $perPage);
        }

        $response = [
            'jokes' => $jokes,
            'page' => $page + 1,
            'jokesPerPage' => $perPage,
            'total' => $qty,
        ];
        if (!is_null($filter)) {
            $response['filter'] = $filter;
        }
        return $this->respond($response);
    }

    /**
     * Create a Joke
     *
     * @OA\Post(
     *     @OA\RequestBody(
     *         description="",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="punchline",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @OA\Response(
     *     response=201,
     *     description="Create a Joke",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref=@Model(type=Joke::class))
     *     )
     * )
     *
     * @OA\Response(
     *     response=417,
     *     description="Required attribute 'punchline' not provided",
     *     @OA\MediaType(
     *         mediaType="application/json"
     *     )
     * )
     */
    public function create(Request $request): Response
    {
        $form = $this->buildForm(JokeType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->respond("Required attribute 'punchline' not provided", Response::HTTP_EXPECTATION_FAILED);
        }

        /** @var Joke $joke */
        $joke = $form->getData();
        $this->getDoctrine()->getManager()->persist($joke);
        $this->getDoctrine()->getManager()->flush();

        return $this->respond($joke, Response::HTTP_CREATED);
    }

    /**
     * Return Joke by ID
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of Jokes to return"
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Return Joke by ID",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref=@Model(type=Joke::class))
     *     )
     * )
     *
     * @OA\Response(
     *     response=404,
     *     description="Joke with ID not found",
     * )
     */
    public function show(Request $request, int $id)
    {
        $joke = $this->getDoctrine()->getRepository(Joke::class)->find($id);

        if (!$joke) {
            return $this->respond("No joke found for ID:" . $id, Response::HTTP_NOT_FOUND);
        }

        return $this->respond($joke);
    }

    /**
     * Update Joke by ID
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of Jokes to update"
     * )
     *
     * @OA\Put(
     *     @OA\RequestBody(
     *         description="",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="punchline",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @OA\Response(
     *     response=202,
     *     description="Update Joke by ID",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref=@Model(type=Joke::class))
     *     )
     * )
     *
     * @OA\Response(
     *     response=404,
     *     description="Joke with ID not found",
     * )
     *
     * @OA\Response(
     *     response=417,
     *     description="Required attribute 'punchline' not provided",
     * )
     */
    public function update(Request $request, int $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $joke = $entityManager->getRepository(Joke::class)->find($id);

        if (!$joke) {
            return $this->respond("No joke found for ID:" . $id, Response::HTTP_NOT_FOUND);
        }

        $decodedJoke = json_decode($request->getContent(),true);
        if (!is_null($decodedJoke) && array_key_exists('punchline', $decodedJoke)) {
            $joke->setPunchline($decodedJoke['punchline']);
            $entityManager->flush();
        } else {
            return $this->respond("Required attribute 'punchline' not provided", Response::HTTP_EXPECTATION_FAILED);
        }

        return $this->respond($joke, Response::HTTP_ACCEPTED);
    }

    /**
     * Delete Joke by ID
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of Jokes to delete"
     * )
     *
     * @OA\Response(
     *     response=202,
     *     description="Joke with ID deleted",
     * )
     *
     * @OA\Response(
     *     response=404,
     *     description="Joke with ID not found",
     * )
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function delete(Request $request, int $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $joke = $entityManager->getRepository(Joke::class)->find($id);

        if (!$joke) {
            return $this->respond("No joke found for ID:" . $id, Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($joke);
        $entityManager->flush();

        return $this->respond("Joke at ID {$id} has been deleted.", Response::HTTP_ACCEPTED);
    }
}