<template>
  <v-container fluid>
    <v-row 
      justify="center"
    >
      <v-col
        cols="4"
        md="8"
        xl="6"
        align="center"
      >
        <h1>{{ $t('title') }}</h1>
      </v-col>
    </v-row>
    <v-row justify="center">
      <v-col class="col-4">
        <v-alert
          v-model="error"
          type="error"
        >
          {{ $t('errors.badCredentials') }}
        </v-alert>
        <v-form
          id="formSecuredSignIn"
          ref="form"
          v-model="valid"
          lazy-validation
          :action="$t('urlSignIn')+this.communityId"
          method="POST"
        >
          <v-text-field
            v-model="credential1"
            name="credential1"
            :label="$t('credential1')"
            :rules="credentialsRules"
            required
          />

          <v-text-field
            v-model="credential2"
            name="credential2"
            :label="$t('credential2')"
            :rules="credentialsRules"
            required
          />

          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <div v-on="(userId === null) ? on : {}">
                <v-btn
                  :disabled="!valid || userId === null"
                  color="success"
                  type="submit"
                  rounded
                  @click="validate"
                >
                  {{ $t('join') }}
                </v-btn>
              </div>
            </template>
            <span>{{ $t("errors.notLogged") }}</span>
          </v-tooltip>          
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/community/CommunitySecuredSignIn.json";


export default {
  i18n: {
    messages: Translations,
    sharedMessages: CommonTranslations
  },
  props:{
    communityId: {
      type: Number,
      default: null
    },
    userId:{
      type: Number,
      default: null
    },
    error:{
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      valid:false,
      credential1:"",
      credential2:"",
      credentialsRules: [
        v => !!v || this.$t("errors.required"),
      ]
    }
  },
  methods: {
    validate() {
      event.preventDefault();
      if (this.$refs.form.validate()) {
        document.getElementById("formSecuredSignIn").submit();

      }
    },
  }
}
</script>