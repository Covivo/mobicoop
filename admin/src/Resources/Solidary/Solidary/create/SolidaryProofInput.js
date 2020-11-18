import React from 'react';
import PropTypes from 'prop-types';
import {
  SelectInput,
  TextInput,
  BooleanInput,
  FileInput,
  FileField,
  RadioButtonGroupInput,
  required,
} from 'react-admin';

const SolidaryProofInput = ({ record, ...rest }) => {
  const validate = !!record.mandatory
    ? [(value) => (!value ? 'Cette preuve est obligatoire' : undefined), required()]
    : undefined;

  const source = `proofs.${record.id}`;

  if (record.checkbox) {
    return <BooleanInput validate={validate} label={record.label} source={source} {...rest} />;
  }

  if (record.input) {
    return <TextInput validate={validate} label={record.label} source={source} {...rest} />;
  }

  if (record.selectbox) {
    const selectboxLabels = (record.options || '').split(';');

    return (
      <SelectInput
        label={record.label}
        source={source}
        validate={validate}
        choices={(record.acceptedValues || '')
          .split(';')
          .map((v, i) => ({ id: v, name: selectboxLabels[i] }))}
        {...rest}
      />
    );
  }

  if (record.file) {
    return (
      <FileInput
        source={source}
        label={record.label}
        validate={validate}
        accept="application/pdf"
        {...rest}
      >
        <FileField source="src" title="title" />
      </FileInput>
    );
  }

  if (record.radio) {
    const selectBoxLabels = (record.options || '').split(';');
    return (
      <RadioButtonGroupInput
        source={source}
        label={record.label}
        choices={(record.acceptedValues || '')
          .split(';')
          .map((v, i) => ({ id: v, name: selectBoxLabels[i] }))}
        {...rest}
        validate={validate}
        fullWidth
      />
    );
  }

  return null;
};

SolidaryProofInput.propTypes = {
  record: PropTypes.object.isRequired,
};

export default SolidaryProofInput;
