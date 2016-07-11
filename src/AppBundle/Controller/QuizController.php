<?php

namespace AppBundle\Controller;

use Doctrine\Common\Cache\PredisCache;
use Predis\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;

class QuizController extends Controller
{
    private $languages = ['en', 'ru'];

    const FIRST_QUESTION_INDEX = 0;
    const RANDOM_QUESTIONS_COUNT = 3;

    const QUESTIONS_KEY = 'questions_';
    const CURRENT_QUESTION_KEY = 'current_question';
    const ATTEMPTS_KEY = 'attempts';

    /**
     * @Route("/", name="homepage")
     * @Route("/question", name="question")
     */
    public function indexAction()
    {
        $questions = $this->getQuestions();

        return $this->render('AppBundle:Quiz:index.html.twig', [
            'question' => $questions[self::FIRST_QUESTION_INDEX],
            'questions' => $questions
        ]);
    }

    private function getQuestions()
    {
        $key = self::QUESTIONS_KEY . $this->get('session')->getId();
        $cache = new PredisCache(new Client());
        if (!$cache->contains($key)) {
            $questions = $this->getDoctrine()
                ->getRepository('AppBundle:Question')
                ->findAll();

            $cache->save($key, $questions);
        } else {
            $questions = $cache->fetch($key);
        }

        return $this->prepareQuestions($questions);
    }

    private function prepareQuestions($questions)
    {
        $prepareQuestions = [];
        foreach ($questions as $question) {
            $prepareQuestions[] = [
                'question' => $question,
                'answers' => $this->getAnswers($question, $questions),
                'lang' => $this->languages[array_rand($this->languages)]
            ];
        }
        return $prepareQuestions;
    }

    private function getAnswers($question, $questions)
    {
        foreach ($questions as $index => $element) {
            if ($question['id'] === $element['id']) {
                unset($questions[$index]);
            }
        }
        shuffle($questions);
        $questions = array_slice($questions, 0, self::RANDOM_QUESTIONS_COUNT);
        $questions[] = $question;
        shuffle($questions);

        return $questions;
    }

    /**
     * @Route("/question/answer", name="answer")
     * @Method({"POST"})
     */
    public function answerAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new Response('', 403);
        }

        $currentQuestion = $this->getQuestion(
            $this->getCurrentQuestionIndex()
        );
        if ($currentQuestion[$currentQuestion['lang']] === $request->get('answer')) {
            return json_encode($this->getNextQuestion());
        } else {
            $this->addAttempt();
        }
    }

    private function getQuestion($index)
    {
        return $this->getQuestions()[$index];
    }

    private function getNextQuestion()
    {
        $session = $this->get('session');
        $session->set(self::CURRENT_QUESTION_KEY, $this->getCurrentQuestionIndex() + 1);

        return $this->getQuestion($this->getCurrentQuestionIndex());
    }

    private function getCurrentQuestionIndex()
    {
        $session = $this->get('session');
        return $session->get(self::CURRENT_QUESTION_KEY);
    }

    private function addAttempt()
    {
        $attempts = $this->get('session')->get(self::ATTEMPTS_KEY);
        if ($attempts > 3) {
            return new Response('Закончен лимит попыток, тест завершён', 403);
        } else {
            $this->get('session')->set(self::ATTEMPTS_KEY, $attempts + 1);
        }
    }
}