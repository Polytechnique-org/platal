    <tr>
      <td colspan="5" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="radio" name="{$name}" value="public" {if $val eq 'public'}checked="checked"{/if} />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="orange">
              <input type="radio" name="{$name}" value="ax" {if $val eq 'ax'}checked="checked"{/if} />
            </td>
            <td class="texte">
              transmis à l'AX
            </td>
            <td class="rouge">
              <input type="radio" name="{$name}" value="private" {if $val eq 'private'}checked="checked"{/if} />
            </td>
            <td class="texte">
              prive
            </td>
            <td class="texte">
              <a href="{"docs/faq.php"|url}#flags" class="popup_800x240">Quelle couleur ??</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
