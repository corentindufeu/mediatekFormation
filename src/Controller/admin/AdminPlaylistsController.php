<?php
namespace App\Controller\admin;

use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use App\Entity\Playlist;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of PlaylistsController
 *
 * @author Corentin Dufeu
 */
class AdminPlaylistsController extends AbstractController
{
    /**
    * Chemin du template de la page playlists
    */
    private const CHEMINPLAYLISTSTEMPLATE = "admin/admin.playlists.html.twig";
    
    /**
     * @var PlaylistRepository
     */
    private $playlistRepository;
    
    /**
     * @var FormationRepository
     */
    private $formationRepository;
    
    /**
     * @var CategorieRepository
     */
    private $categorieRepository;
    
    public function __construct(
        PlaylistRepository $playlistRepository,
        CategorieRepository $categorieRepository,
        FormationRepository $formationRespository
    ) {
        $this->playlistRepository = $playlistRepository;
        $this->categorieRepository = $categorieRepository;
        $this->formationRepository = $formationRespository;
    }
    
    /**
     * @Route("/admin/playlists", name="admin.playlists")
     * @return Response
     */
    public function index(): Response
    {
        $playlists = $this->playlistRepository->findAllOrderByName('ASC');
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINPLAYLISTSTEMPLATE, [
            'playlists' => $playlists,
            'categories' => $categories
        ]);
    }

    /**
    * @Route("/admin/playlists/tri/{champ}/{ordre}", name="admin.playlists.sort")
    * @param type $champ
    * @param type $ordre
    * @return Response
    */
    public function sort($champ, $ordre): Response{
        switch($champ){
            case "name":
                $playlists = $this->playlistRepository->findAllOrderByName($ordre);
                break;
            case "nbformations":
                $playlists = $this->playlistRepository->findAllOrderByNbFormations($ordre);
                break;
        }
        $categories = $this->categorieRepository->findAll();
        return $this->render("admin/admin.playlists.html.twig", [
            'playlists' => $playlists,
            'categories' => $categories
        ]);
    }
    
    /**
     * @Route("/admin/playlists/recherche/{champ}/{table}", name="admin.playlists.findallcontain")
     * @param type $champ
     * @param Request $request
     * @param type $table
     * @return Response
     */
    public function findAllContain($champ, Request $request, $table=""): Response
    {
        $valeur = $request->get("recherche");
        $playlists = $this->playlistRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINPLAYLISTSTEMPLATE, [
            'playlists' => $playlists,
            'categories' => $categories,
            'valeur' => $valeur,
            'table' => $table
        ]);
    }
    
    /**
     * @Route("/admin/playlists/playlist/{id}", name="admin.playlists.showone")
     * @param type $id
     * @return Response
     */
    public function showOne($id): Response
    {
        $playlist = $this->playlistRepository->find($id);
        $playlistCategories = $this->categorieRepository->findAllForOnePlaylist($id);
        $playlistFormations = $this->formationRepository->findAllForOnePlaylist($id);
        return $this->render("admin/playlist.html.twig", [
            'playlist' => $playlist,
            'playlistcategories' => $playlistCategories,
            'playlistformations' => $playlistFormations
        ]);
    }
    
    /**
     * @Route("/admin/playlists/deletePlaylist/{id}", name="admin.playlist.deleteone")
     * @param int $id
     * @return Response
     */
    public function deleteOne(int $id): Response
    {
        $playlist = $this->playlistRepository->find($id);
        $formations = $playlist->getFormations();
        if(count($formations) == 0) {
            $this->playlistRepository->remove($playlist, true);
        }
        return $this->redirectToRoute('admin.playlists');
    }
    
    /**
     * @Route("/admin/playlists/editPlaylist/{id}", name="admin.playlist.editone")
     * @param int $id
     * @return Response
     */
    public function editOne(int $id) : Response {
        $formations = $this->formationRepository->findAll();
        $playlist = $this->playlistRepository->find($id);
        return $this->render("admin/admin.playlist.edit.html.twig", [
            'formations' => $formations,
            'playlist' => $playlist,
        ]);
    }
    
    /**
     * @Route("/admin/playlists/add/", name="admin.playlist.addone")
     * @param Request $request
     * @return Response
     */
    public function addOne() : Response {
        $playlist = $this->playlistRepository->findAll();
        return $this->render("admin/admin.playlist.add.html.twig", [
            'playlist' => $playlist,
        ]);
    }
    
    /**
     * @Route("/admin/playlists/register/{id}", name="admin.playlist.registerone")
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function registerOne(Request $request, int $id = null) : Response {
        $nameInput = $request->get("nameInput");
        $descriptionTextArea = $request->get("descriptionTextArea");
        if(isset($nameInput)) {
            if($id != null) {
                $playlist = $this->playlistRepository->find($id);
            } else {
                $playlist = new Playlist();
            }
            $playlist->setName($nameInput);
            $playlist->setDescription($descriptionTextArea);
            $this->playlistRepository->add($playlist, true);
        }
        return $this->redirectToRoute('admin.playlists');
    }
    
}
