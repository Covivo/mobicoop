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
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          align="center"
          cols="12"
        >
          <p v-html="errorDisplay" />
        </v-col>
      </v-row>
      <v-row class="mt-n8 mb-n8 mr-n8">
        <v-col
          v-col
          cols="12"
          align="end"
        >
          <v-btn
            color="white"
            text
            @click="snackbar = false"
          >
            <v-icon>mdi-close-circle-outline</v-icon>
          </v-btn>
        </v-col>
      </v-row>
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
            <p>{{ textCheckbox1 }}</p>
            <v-checkbox
              v-model="checkbox1"
              :label="$t('checkboxes.label')"
              required
            />
          </v-col>
        </v-row>
        <v-row v-if="showCheckbox2">
          <v-col>
            <p>{{ textCheckbox2 }}</p>
            <v-checkbox
              v-model="checkbox2"
              :label="$t('checkboxes.label')"
            />
          </v-col>
        </v-row>
        <v-row v-if="showCheckbox3">
          <v-col>
            <p>{{ textCheckbox3 }}</p>
            <v-checkbox
              v-model="checkbox3"
              :label="$t('checkboxes.label')"
            />
          </v-col>
        </v-row>
        <v-row v-if="showCheckbox4">
          <v-col>
            <p v-html="textCheckbox4" />
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
            {{ $t('buttons.confirmParentalConsent.label') }}
          </v-btn>
        </v-row>
      </v-col>
    </v-row>
    <v-dialog
      v-model="activeDialog"
      persistent
      max-width="600"
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
import moment from "moment";
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
      snackbar:false,
      textCheckbox1: '',
      textCheckbox2: '',
      textCheckbox3: '',
      textCheckbox4: ''
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
          if (this.user.parentalConsentDate != null) {
            this.dialogParentalConsentAlreadyGiven(this.user)
          } else {
            this.selectCheckboxText(this.user.gender);
            this.showForm = true;
          }
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
          this.dialogSuccess(this.user);
        })
        .catch((error) => {
          this.treatErrorMessage(this.$t('errorWhenGiveParentalConsent'))
          console.log(error);
        });
    },
    dialogSuccess(user) {
      this.activeDialog = true;
      this.dialog.title = this.$t('dialogParentalConsentSuccess.title');
      this.dialog.content = this.$t('dialogParentalConsentSuccess.content',{'givenName':user.givenName, 'familyName':user.familyName, 'platformName':this.platformName});
      this.dialog.buttonLabel= this.$t('buttons.dialogParentalConsentSuccess.label');
      this.dialog.buttonRoute= this.$t('buttons.dialogParentalConsentSuccess.route');
    },
    dialogParentalConsentAlreadyGiven(user) {
      this.activeDialog = true;
      let parentalConsentDate = moment(user.parentalConsentDate.date).format(this.$t("i18n.date.format"));
      this.dialog.title = this.$t('dialogParentalConsentAlreadyGiven.title');
      this.dialog.content = this.$t('dialogParentalConsentAlreadyGiven.content',{'givenName':user.givenName, 'familyName':user.familyName, 'consentDate':parentalConsentDate});
      this.dialog.buttonLabel= this.$t('buttons.dialogParentalConsentAlreadyGiven.label');
      this.dialog.buttonRoute= this.$t('buttons.dialogParentalConsentAlreadyGiven.route');
    },
    treatErrorMessage(errorMessage) {
      this.errorDisplay = errorMessage;
      this.snackbar= true;
      this.parentalConsentToken = null;
    },
    selectCheckboxText(gender) {
      switch (gender) {
      case 1:
        this.textCheckbox1 = this.$t('checkboxes.female.1',{'givenName':this.user.givenName, 'familyName': this.user.familyName, 'platformName':this.platformName});
        this.textCheckbox2 = this.$t('checkboxes.female.2',{'platformName':this.platformName});
        this.textCheckbox3 = this.$t('checkboxes.female.3');
        this.textCheckbox4 = this.$t('checkboxes.female.4',{'platformName':this.platformName});
        break;
      case 2:
        this.textCheckbox1 = this.$t('checkboxes.male.1',{'givenName':this.user.givenName, 'familyName': this.user.familyName, 'platformName':this.platformName});
        this.textCheckbox2 = this.$t('checkboxes.male.2',{'platformName':this.platformName});
        this.textCheckbox3 = this.$t('checkboxes.male.3');
        this.textCheckbox4 = this.$t('checkboxes.male.4',{'platformName':this.platformName});
        break;
      default:
        this.textCheckbox1 = this.$t('checkboxes.other.1',{'givenName':this.user.givenName, 'familyName': this.user.familyName, 'platformName':this.platformName});
        this.textCheckbox2 = this.$t('checkboxes.other.2',{'platformName':this.platformName});
        this.textCheckbox3 = this.$t('checkboxes.other.3');
        this.textCheckbox4 = this.$t('checkboxes.other.4',{'platformName':this.platformName});
        break;
      }

    }
  }
};
</script>
