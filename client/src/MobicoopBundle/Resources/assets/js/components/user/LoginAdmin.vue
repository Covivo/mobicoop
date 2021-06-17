<template>
  <v-container
    fluid
    style="height: 100%"
  >
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
        align="center"
      >
        <h1>{{ $t('title', {user: userEmail}) }}</h1>
      </v-col>
    </v-row>
    <div class="mt-6 pt-6">
      <v-row
        justify="center"
        align="center"
        class="text-center"
      >
        <v-col class="col-4">
          <v-form
            ref="form"
            v-model="valid"
            lazy-validation
            method="POST"
          >
            <v-text-field
              v-model="userEmail"
              :rules="userRules"
              :label="$t('userEmail')"
              name="emailDelegate"
              required
            />
            <v-text-field
              v-model="adminEmail"
              :rules="adminRules"
              :label="$t('adminEmail')"
              name="email"
              required
            />
            <v-text-field
              v-model="password"
              :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
              :rules="passwordRules"
              :type="show1 ? 'text' : 'password'"
              name="password"
              :label="$t('password')"
              @click:append="show1 = !show1"
            />
            <v-checkbox
              v-model="validation"
              class="check mt-12"
              color="primary"
              :rules="checkboxRules"
              required
            >
              <template v-slot:label>
                <div v-html="$t('checkbox.text', {user: userEmail})" />
              </template>
            </v-checkbox>
            <v-btn
              :disabled="!valid || !validation || !password"
              :loading="loading"
              color="secondary"
              type="submit"
              rounded
              @click="validate"
            >
              {{ $t('connection', {user: userEmail}) }}
            </v-btn>
          </v-form>
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>
<script>
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/LoginAdmin/";

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
    email: {
      type: String,
      default: null
    },
    delegateEmail: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      valid: true,
      loading: false,
      show1: false,
      userEmail: this.delegateEmail,
      userRules: [
        v => !!v || this.$t("userEmailRequired"),
        v => /.+@.+/.test(v) || this.$t("userEmailInvalid")
      ],
      adminEmail: this.email,
      adminRules: [
        v => !!v || this.$t("adminEmailRequired"),
        v => /.+@.+/.test(v) || this.$t("adminEmailInvalid")
      ],
      password: null,
      passwordRules: [
        v => !!v || this.$t("passwordRequired")
      ],
      checkboxRules: [
        (v) => !!v || this.$t("checkbox.required", {user: this.userEmail}),
      ],
      validation: false,
    };
  },
  methods: {
    validate(e) {
      if (this.$refs.form.validate()) {
        this.loading = true;
      }
    }
  }
};
</script>