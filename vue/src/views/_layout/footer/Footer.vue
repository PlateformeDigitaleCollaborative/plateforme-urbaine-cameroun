<template>
  <footer class="Footer">
    <div class="Footer__ctn container">
      <div class="Footer__blocks">
        <div class="Footer__block Footer__block--left">
          <div class="Footer__contact">
            <h4 class="Footer__title">{{ $t('footer.contact') }}</h4>
            <div class="Footer__contactLogos">
              <img
                loading="lazy"
                src="@/assets/images/footer/facebook.svg"
                alt="facebook"
                class="Footer__contactImg ml-8"
                @click="openFacebook"
              />
              <img
                loading="lazy"
                src="@/assets/images/footer/linkedin.svg"
                alt="linkedin"
                class="Footer__contactImg"
                @click="openLinkedIn"
              />
              <img
                loading="lazy"
                src="@/assets/images/footer/instagram.svg"
                alt="instagram"
                class="Footer__contactImg"
                @click="openInstagram"
              />
            </div>
          </div>
          
          <div class="Footer__adressCtn Footer__adressCtn--clickable" @click="openOfficeOnMap">
            <v-icon icon="$mapMarkerOutline"></v-icon>
            <div class="Footer__adress">
              <span class="font-weight-medium">Bureau d'Expertise France au Cameroun</span>
              <span>Quartier Bastos</span>
              <span>Rue 1.828</span>
              <span>Yaoundé</span>
            </div>
            </div>
            <div class="Footer__adressCtn">
            <v-icon icon="$phoneOutline"></v-icon>
            <div class="Footer__adress">
              <span>00 237 6 52 26 66 18</span>
            </div>
          </div>
          <v-btn
            color="main-blue"
            class="Footer__btn"
            variant="flat"
            prepend-icon="$emailOutline"
            append-icon="$arrowRight"
            onclick="location.href='mailto:plateformeurbaine@gmail.com';"
            >{{ $t('footer.email') }}</v-btn
          >
        </div>
        <div class="Footer__block Footer__block--right">
          <h4 class="Footer__title">{{ $t('footer.partners') }}</h4>
          <div class="Footer__partnersLogos">
            <img
              loading="lazy"
              src="@/assets/images/footer/cameroun_flag.svg"
              alt="cameroun flag"
            />
            <a href="https://www.eeas.europa.eu/delegations/cameroun_fr?s=88" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/logo_ue.png" alt="union-europeenne"
            /></a>
            <a href="https://www.expertisefrance.fr/fiche-projet?id=882931" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/logo_ef.png" alt="expertise-france"
            /></a>
          </div>
          <div class="Footer__secondaryPartnersLogos">
            <a href="https://www.minhdu.gov.cm/" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/minhdu.png" alt="minhdu"
            /></a>
            <a href="https://www.minddevel.gov.cm" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/mindevel.png" alt="minddevel"
            /></a>
            <a href="https://minepat.gov.cm/fr/accueil" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/minepat.png" alt="minepat"
            /></a>
            <a href="https://tresorpublic.cm/fr/ministries/mindcaf" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/mindcaf.png" alt="mindcaf"
            /></a>
            <a href="https://cvuc-uccc.com" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/cvuc.png" alt="cvuc"
            /></a>
             <a href="https://www.osidimbea.cm" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/amvc.png" alt="amvc"
            /></a>
            <a href="https://arc.cm" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/arc.png" alt="arc"
            /></a>
            <a href="https://www.facebook.com/p/Communaut%C3%A9-Urbaine-de-Douala-100068953398194/?locale=fr_FR" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/douala.png" alt="douala"
            /></a>
            <a href="https://www.facebook.com/groups/2611030598941314" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/maroua.png" alt="maroua"
            /></a>
            <a href="https://yaounde.cm" target="_blank"
              ><img loading="lazy" src="@/assets/images/footer/yaounde.png" alt="yaounde"
            /></a>
          </div>
        </div>
      </div>
      <div class="Footer__links">
        <span @click="openLegal">{{ $t('footer.legal') }}</span>
        <span>{{ $t('footer.privacy') }}</span>
        <span>{{ $t('footer.cgu') }}</span>
        <span>{{ $t('footer.plan') }}</span>
        <span>{{ $t('footer.cookies') }}</span>
        <span @click="openFAQ">{{ $t('header.help') }}</span>
        <a :href="whatsappLink" target="_blank">{{ $t('header.contact') }}</a>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'


const router = useRouter()
const whatsappLink = `https://wa.me/${'+237652266618'.replace(/\D/g, '')}`

// Coordonnées du bureau (Quartier Golf Bastos, Yaoundé), trouvées via recherche —
// à ajuster si vous avez une source plus précise (ex. relevé GPS interne).
const OFFICE_COORDINATES = { lat: 3.900561, lng: 11.513224 }

function openFacebook() {
  window.open('https://www.facebook.com/share/v/19H64XuEmM/?mibextid=wwXIfr', '_blank')
}
function openLinkedIn() {
  window.open('https://www.linkedin.com/in/plateforme-urbaine-au-cameroun-197077357/', '_blank')
}
function openInstagram() {
  window.open('https://www.instagram.com/reel/DJKM01XqfN8/?igsh=MWNneGYxMDNyemdrOQ==', '_blank')
}
function openLegal() {
  const pdfUrl = 'Mentions_legales.pdf'
  window.open(pdfUrl, '_blank')
}
function openFAQ() {
  const pdfUrl = '/docs/FAQ.pdf'
  window.open(pdfUrl, '_blank')
}

function openOfficeOnMap() {
  router.push({
    name: 'map',
    query: {
      focusLat: String(OFFICE_COORDINATES.lat),
      focusLng: String(OFFICE_COORDINATES.lng),
      focusLabel: 'Expertise France'
    }
  })
}
</script>

<style lang="scss">
.Footer {
  background-image: url('@/assets/images/Frise.jpg');
  background-repeat: repeat-x;
  width: 100%;
  padding: 6rem 0 2rem 0;
  position: relative;

  &::after {
    content: '';
    z-index: -1;
    background: linear-gradient(to top, transparent 0%, rgb(var(--v-theme-light-yellow)) 100%);
    position: absolute;
    inset: 0;
    height: 8rem;
  }

  .Footer__ctn {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 2rem;

    .Footer__blocks {
      display: flex;
      flex-flow: row nowrap;
      justify-content: space-between;
      gap: 6rem;

      .Footer__block {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;

        &--left {
          gap: 1.5rem;
          flex: 1 0 auto;
        }

        .Footer__title {
          color: rgb(var(--v-theme-main-blue));
        }

        .Footer__contactLogos {
          display: flex;
          align-items: center;
          flex-flow: row nowrap;
          gap: 1rem;

          .Footer__contactImg {
            --dim-contact-img: 2rem;
            width: var(--dim-contact-img);
            height: var(--dim-contact-img);
            cursor: pointer;
          }
        }

        .Footer__contact {
          display: flex;
          align-items: center;
          width: 100%;
          color: rgb(var(--v-theme-main-blue));
          font-weight: 700;
        }

        .Footer__adressCtn {
          display: flex;
          flex-direction: row;
          padding: 0 1rem;
          gap: 0.5rem;
        }

        .Footer__adress {
          display: flex;
          flex-direction: column;
          gap: 0.25rem;
        }

        .Footer__adressCtn--clickable {
          cursor: pointer;

          &:hover {
            .Footer__adress span {
              text-decoration: underline;
            }
          }
        }

        .Footer__partner {
          height: 2.5rem;
          display: flex;
          align-items: center;
          color: rgb(var(--v-theme-main-blue));
          font-weight: 700;
        }

        .Footer__partnersLogos {
          display: flex;
          flex-flow: row nowrap;
          gap: 2rem;
          margin-top: 1rem;

          & > img {
            border: solid 1px rgb(var(--v-theme-main-blue));
          }

          img {
            height: 4.5rem;
            cursor: pointer;
          }
        }

        .Footer__btn {
          width: fit-content;
          height: 2.5rem;

          span {
            font-weight: 400;
          }
        }

        .Footer__secondaryPartnersLogos {
          margin-top: 3rem;
          display: flex;
          flex-flow: row nowrap;
          align-items: center;
          justify-content: space-between;
          gap: 1rem;

          a {
            flex: 1 1 0; // se répartissent l'espace dispo pour tenir sur une ligne sans déborder
            display: flex;
            justify-content: center;
            min-width: 0;
          }

          img {
            height: 3.5rem;
            max-width: 100%;
            cursor: pointer;
          }
        }
      }
    }
  }

  .Footer__links {
    $dim-divider: 0.5rem;
    display: flex;
    flex-flow: row wrap;
    margin-top: 2rem;
    gap: $dim-divider;
    align-items: center;

    span,
    a {
      cursor: pointer;
    }

    a {
      text-decoration: none;
      white-space: nowrap;
      color: inherit;
    }

    & > *:not(:last-child) {
      &::after {
        content: '|';
        margin-left: $dim-divider;
      }
    }
  }
}

@media (max-width: $bp-lg) {
  .Footer {
    .Footer__ctn {
      .Footer__blocks {
        width: 100%;
        flex-direction: column-reverse;

        .Footer__block {
          &--left {
            width: 100%;
            padding-right: 0px;
          }

          &--right {
            width: 100%;
          }

          .Footer__title {
            text-align: center;
            font-size: $font-size-h3;
          }

          .Footer__contactLogos {
            .Footer__contactImg {
            }
          }

          .Footer__contact {
            margin-top: 1rem;
            flex-direction: column;
            font-size: $font-size-h3;
            gap: 1rem;
          }

          .Footer__partner {
            justify-content: center;
            font-size: $font-size-h3;
          }

          .Footer__partnersLogos {
            gap: 1rem;
            justify-content: center;

            img {
              height: 4rem;
            }
          }

          .Footer__btn {
            width: 100%;
          }

          .Footer__secondaryPartnersLogos {
            margin-top: 1rem;
            height: auto;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;

            a {
              flex: none; // on annule le "flex: 1 1 0" du desktop, chaque logo garde sa taille naturelle
            }

            img {
              height: 3.5rem;
            }
          }
        }
      }
    }

    .Footer__links {
      justify-content: center;
    }
  }
}
</style>
