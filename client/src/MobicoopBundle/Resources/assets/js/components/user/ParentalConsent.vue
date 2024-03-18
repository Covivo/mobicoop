<template>
  <v-container
    id="scroll-target"
    fluid
  >
    <v-snackbar
      v-model="snackbar"
      color="error"
      top
      timeout="20000"
      vertical
    >
      <p v-html="errorDisplay" />
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>
    <v-row justify="center">
      <v-col
        cols="12"
        md="8"
        xl="6"
        align="center"
      >
        <h1>{{ $t('title') }}</h1>
      </v-col>
    </v-row>
    <v-row
      v-if="!user"
      justify="center"
      align="center"
    >
      <v-col
        cols="4"
        align="center"
      >
        <v-text-field
          id="parentalConsentToken"
          v-model="parentalConsentToken"
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
            {{ $t('buttons.giveParentalConsent.label') }}
          </v-btn>
        </v-row>
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
            <p>{{ $t('checkboxes.1',{'givenName':user.givenName, 'familyName': user.familyName, 'platformName':platformName}) }}</p>
            <v-checkbox
              v-model="checkbox1"
              :label="$t('checkboxes.label')"
              required
            />
          </v-col>
        </v-row>
        <v-row v-if="showCheckbox2">
          <v-col>
            <p>{{ $t('checkboxes.2',{'platformName':platformName}) }}</p>
            <v-checkbox
              v-model="checkbox2"
              :label="$t('checkboxes.label')"
            />
          </v-col>
        </v-row>
        <v-row v-if="showCheckbox3">
          <v-col>
            <p>{{ $t('checkboxes.3') }}</p>
            <v-checkbox
              v-model="checkbox3"
              :label="$t('checkboxes.label')"
            />
          </v-col>
        </v-row>
        <v-row v-if="showCheckbox4">
          <v-col>
            <p v-html="$t('checkboxes.4',{'platformName':platformName})" />
            <v-checkbox
              v-model="checkbox4"
              :label="$t('checkboxes.label')"
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
            @click="giveParentalConsent"
          >
            {{ $t('buttons.confirmParentalConsentGiven.label') }}
          </v-btn>
        </v-row>
      </v-col>
    </v-row>
    <v-dialog
      v-model="activeDialog"
      persistent
      max-width="900"
    >
      <v-card>
        <v-card-title
          class="text-h5 justify-center"
        >
          {{ dialog.title }}
        </v-card-title>
        <v-card-text v-html="dialog.content" />
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="secondary"
            primary
            rounded
            :href="dialog.buttonRoute"
          >
            {{ dialog.buttonLabel }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
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
    },
    platformName:{
      type: String,
      default:'Mobicoop'
    },
  },
  data() {
    return {
      parentalConsentToken: null,
      showForm: false,
      checkbox1: false,
      checkbox2: false,
      checkbox3: false,
      checkbox4: false,
      user: null,
      activeDialog: false,
      dialog: {
        title: '',
        content: '',
        buttonLabel: '',
        buttonAction: null
      },
      errorDisplay: "",
      snackbar:false
    };
  },
  computed: {
    formButtondisabled() {
      return !this.parentalConsentToken || this.showForm == true;
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
    }
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
  methods: {
    getUserUnder18() {
      let params = {
        'token':this.parentalConsentToken
      }
      maxios.post(this.$t("getUserUnderEighteenUrl"), params)
        .then(res => {
          this.user = res.data;
          this.showForm = true;
        })
        .catch((error) => {
          this.treatErrorMessage(this.$t('errorWhenGetUser'))
          console.error(error);
        });
    },
    giveParentalConsent(){
      let params = {
        'uuid':this.uuid,
        'token':this.parentalConsentToken
      }
      maxios.post(this.$t("giveParentalConsentUrl"), params)
        .then(res => {
          this.user = res.data;
          this.dialogSuccess();
        })
        .catch((error) => {
          this.treatErrorMessage(this.$t('errorWhenGiveParentalConsent'))
          console.log(error);
        });
    },
    dialogSuccess() {
      this.activeDialog = true;
      this.dialog.title = this.$t('dialogParentalConsentGiven.title');
      this.dialog.content = this.$t('dialogParentalConsentGiven.content',{'givenName':this.user.givenName, 'familyName':this.user.givenName, 'platformName':this.platformName});
      this.dialog.buttonLabel= this.$t('buttons.dialogParentalConsentGiven.label');
      this.dialog.buttonRoute= this.$t('buttons.dialogParentalConsentGiven.route');
    },
    treatErrorMessage(errorMessage) {
      this.errorDisplay = errorMessage;
      this.snackbar= true;
      this.parentalConsentToken = null;
    }
  }
};
</script>
