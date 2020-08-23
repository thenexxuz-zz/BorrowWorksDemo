<?php
namespace App\Controller;

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
     * @param Request $request
     * @return Response
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
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $form = $this->buildForm(JokeType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->respond("Required attribute 'punchline' not provided", Response::HTTP_BAD_REQUEST);
        }

        /** @var Joke $joke */
        $joke = $form->getData();
        $this->getDoctrine()->getManager()->persist($joke);
        $this->getDoctrine()->getManager()->flush();

        return $this->respond($joke, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function show(Request $request, int $id)
    {
        $joke = $this->getDoctrine()->getRepository(Joke::class)->find($id);
        return $this->respond($joke);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, int $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $joke = $entityManager->getRepository(Joke::class)->find($id);

        if (!$joke) {
            return $this->respond("No joke found for ID:" . $id, Response::HTTP_NOT_FOUND);
        }

        $decodedJoke = json_decode($request->getContent(),true);
        if (array_key_exists('punchline', $decodedJoke)) {
            $joke->setPunchline($decodedJoke['punchline']);
            $entityManager->flush();
        } else {
            return $this->respond("Required attribute 'punchline' not provided", Response::HTTP_EXPECTATION_FAILED);
        }

        return $this->respond($joke, Response::HTTP_ACCEPTED);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function delete(Request $request, int $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $joke = $entityManager->getRepository(Joke::class)->find($id);
        $entityManager->remove($joke);
        $entityManager->flush();

        return $this->respond("Joke at ID {$id} has been deleted.", Response::HTTP_ACCEPTED);
    }
}