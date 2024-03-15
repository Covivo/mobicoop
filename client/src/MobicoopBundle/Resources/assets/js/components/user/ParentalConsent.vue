<template>
  <v-container
    id="scroll-target"
    fluid
  >
    <v-row justify="center">
      <v-col
        cols="12"
        md="8"
        xl="6"
        align="center"
      >
        <h1>Autorisation parentale</h1>
      </v-col>
    </v-row>
    <v-row
      justify="center"
      align="center"
    >
      <v-col
        cols="4"
        align="center"
      >
        <v-form>
          <v-text-field
            id="parentalConsentToken"
            v-model="form.parentalConsentToken"
            :rules="form.givenNameRules"
            label="Code"
            required
          />
          <v-row
            justify="center"
            align="center"
            class="mb-25"
          >
            <v-btn
              ref="button"
              rounded
              :disabled="formButtondisabled"
              class="my-13 mr-12"
              color="secondary"
              @click="getUserUnder18"
            >
              Acceder au formulaire
            </v-btn>
          </v-row>
        </v-form>
      </v-col>
    </v-row>
    <v-row
      justify="center"
      align="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
        align="start"
      >
        <v-row v-if="showCheckbox1">
          <v-col>
            <p>Je, responsable légal.e du.de la mineur.e Prénom NOM déclare autoriser ce.cette mineur.e de plus de 14 ans dont j’ai la responsabilité à utiliser Mobicoop. J’ai bien noté que ce.cette mineur.e reste sous mon entière responsabilité.</p>
            <v-checkbox
              v-model="checkbox1"
              label="Lu et approuvé"
              required
            />
          </v-col>
        </v-row>
        <v-row v-if="showCheckbox2">
          <v-col>
            <p>En aucun cas, la SCIC Mobicoop, opérateur de la plateforme, ainsi que les collectivités partenaires de Mobicoop ne pourront être tenues responsables pour quelque dommage que ce soit.</p>
            <v-checkbox
              v-model="checkbox2"
              label="Lu et approuvé"
            />
          </v-col>
        </v-row>
        <v-row v-if="showCheckbox3">
          <v-col>
            <p>Je m’engage à bien expliquer au.à la mineur.e dont j’ai la responsabilité le fonctionnement du dispositif et notamment à attirer son attention sur les conseils pour réussir ses déplacements dans la FAQ et les Bonnes Pratiques. </p>
            <v-checkbox
              v-model="checkbox3"
              label="Lu et approuvé"
            />
          </v-col>
        </v-row>
        <v-row v-if="showCheckbox4">
          <v-col>
            <p>Mobicoop se réserve le droit de me contacter.</p>
            <p>En donnant mon autorisation parentale, je m'engage à respecter chaque paragraphe de celle-ci.</p>
            <v-checkbox
              v-model="checkbox4"
              label="Lu et approuvé"
            />
          </v-col>
        </v-row>
        <v-row
          v-if="showConsentButton"
          justify="center"
          align="center"
          class="mb-25"
        >
          <v-btn
            ref="button"
            rounded
            class="my-13 mr-12"
            :disabled="consentDisabled"
            color="secondary"
            @click="validate"
          >
            Je confirme donner mon autorisation parentale
          </v-btn>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import maxios from "@utils/maxios";

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/ParentalConsent/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    uuid:{
      type: String,
      default:null
    }
  },
  data() {
    return {
      form: {
        parentalConsentToken: null
      },
      showForm: false,
      checkbox1: false,
      checkbox2: false,
      checkbox3: false,
      checkbox4: false,
      user: null
    };
  },
  computed: {
    formButtondisabled() {
      return !this.form.parentalConsentToken;
    },
    consentDisabled() {
      if (this.showForm && this.checkbox1 && this.checkbox2 && this.checkbox3 && this.checkbox4){
        return false;
      }
      else {
        return true;
      }
    },
    showConsentButton() {
      return this.showForm;
    },
    showCheckbox1() {
      return this.showForm;
    },
    showCheckbox2() {
      return (this.showForm && this.checkbox1);
    },
    showCheckbox3() {
      return (this.showForm && this.checkbox1 && this.checkbox2);
    },
    showCheckbox4() {
      return (this.showForm && this.checkbox1 && this.checkbox2 && this.checkbox3);
    },

  },
  watch: {
    checkbox1() {
      if (this.checkbox1 == false) {
        this.checkbox2 = false;
        this.checkbox3 = false;
        this.checkbox4 = false;
      };
    },
    checkbox2() {
      if (this.checkbox2 == false) {
        this.checkbox3 = false;
        this.checkbox4 = false;
      };
    },
    checkbox3() {
      if (this.checkbox3 == false) {
        this.checkbox4 = false;
      };
    },
  },
  created() {
    this.getUserUnder18();
  },
  methods: {
    getUserUnder18() {
      let params = {
        'uuid':this.uuid
      }
      maxios.post(this.$t("getUserUnder18Url"), params)
        .then(res => {
          console.log(res.data);
          this.user = res.data;
        })
        .catch((error) => {
          console.log(error);
        });
      this.showForm = true;
    },
    giveParentalConsent(){
      let params = {
        'uuid':this.uuid,
        'token':this.form.parentalConsentToken
      }
      maxios.post(this.$t("giveParentalConsentUrl"), params)
        .then(res => {
          this.user = res.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },
  }
};
</script>
