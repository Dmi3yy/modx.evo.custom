/**
 * parseChunk
 *
 * @category  parser
 * @version   0.1
 * @license     GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @param string $ChunkName Имя чанка
 * @return string распарсеный чанк
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @internal    @installset base, sample
 *
 * @example [!parseChunk? &ChunkName=`form` &username=`Agel_Nash`!]
 */

return isset($ChunkName) ? $modx->parseChunk($ChunkName, $modx->event->params,'[+','+]') : '';