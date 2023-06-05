<?php
namespace App\Controller\admin;

use App\Repository\CategorieRepository;
use App\Repository\PlaylistRepository;
use App\Repository\FormationRepository;
use App\Entity\Formation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controleur des formations
 *
 * @author Corentin Dufeu
 */
class AdminFormationsController extends AbstractController
{
    /**
    * Chemin du template de la page formations
    */
    private const CHEMINFORMATIONSTEMPLATE = "admin/admin.formations.html.twig";

    /**
     * @var FormationRepository
     */
    private $formationRepository;
    
    /**
     * @var CategorieRepository
     */
    private $categorieRepository;
    
    /**
     * @var PlaylistRepository
     */
    private $playlistRepository;
    
    public function __construct(FormationRepository $formationRepository, 
                CategorieRepository $categorieRepository, 
                PlaylistRepository $playlistRepository
            )
    {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository= $categorieRepository;
        $this->playlistRepository = $playlistRepository;
    }
    
    /**
     * @Route("/admin/formations", name="admin.formations")
     * @return Response
     */
    public function index(): Response
    {
       
        $formations = $this->formationRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINFORMATIONSTEMPLATE, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/admin/formations/tri/{champ}/{ordre}/{table}", name="admin.formations.sort")
     * @param type $champ
     * @param type $ordre
     * @param type $table
     * @return Response
     */
    public function sort($champ, $ordre, $table=""): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINFORMATIONSTEMPLATE, [
            'formations' => $formations,
            'categories' => $categories
        ]);
    }
    
    /**
     * @Route("/admin/formations/recherche/{champ}/{table}", name="admin.formations.findallcontain")
     * @param type $champ
     * @param Request $request
     * @param type $table
     * @return Response
     */
    public function findAllContain($champ, Request $request, $table=""): Response
    {
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINFORMATIONSTEMPLATE, [
            'formations' => $formations,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }
    
    /**
     * @Route("/admin/formations/formation/{id}", name="admin.formations.showone")
     * @param type $id
     * @return Response
     */
    public function showOne($id): Response
    {
        $formation = $this->formationRepository->find($id);
        return $this->render("pages/formation.html.twig", [
            'formation' => $formation
        ]);
    }
    
    /**
     * @Route("/admin/formations/deleteFormation/{id}", name="admin.formations.deleteone")
     * @param int $id
     * @return Response
     */
    public function deleteOne(int $id): Response
    {
        $formation = $this->formationRepository->find($id);
        $this->formationRepository->remove($formation, true);
        return $this->redirectToRoute('admin.formations');
    }
    
    /**
     * @Route("/admin/formations/editFormation/{id}", name="admin.formation.editone")
     * @param int $id
     * @return Response
     */
    public function editOne(int $id) : Response {
        $formation = $this->formationRepository->find($id);
        $playlists = $this->playlistRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render("admin/admin.formation.edit.html.twig", [
            'formation' => $formation,
            'playlists' => $playlists,
            'categories' => $categories,
        ]);
    }
    
    /**
     * @Route("/admin/formations/add/", name="admin.formation.addOne")
     * @param Request $request
     * @return Response
     */
    public function addOne() : Response {
        $playlists = $this->playlistRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render("admin/admin.formation.add.html.twig", [
            'playlists' => $playlists,
            'categories' => $categories,
        ]);
    }
    
    /**
     * @Route("/admin/formations/register/{id}", name="admin.formation.registerone")
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function registerOne(Request $request, int $id = null) : Response {
        $titleInput = $request->get("titleInput");
        $descriptionTextArea = $request->get("descriptionTextArea");
        $playlistSelect = $request->get("playlistsSelect");
        $categoriesSelect = $request->get("categoryInput");
        $videoIdInput = $request->get("videoIdInput");
        if(isset($titleInput) && isset($playlistSelect) && isset($videoIdInput)) {
            if($id != null) {
                $formation = $this->formationRepository->find($id);
                $categoriesFormation = $formation->getCategories();
                foreach($categoriesFormation as $category) {
                    $formation->removeCategory($category);
                }
            } else {
                $formation = new Formation();
            }
            $formation->setTitle($titleInput);
            $formation->setDescription($descriptionTextArea);
            $formation->setPlaylist($this->playlistRepository->find($playlistSelect));
            foreach($categoriesSelect as $categoryId) {
                $category = $this->categorieRepository->find($categoryId);
                $formation->addCategory($category);
            }
            $formation->setVideoId($videoIdInput);
            $this->formationRepository->add($formation, true);
        }
        return $this->redirectToRoute('admin.formations');
    }
    
}
