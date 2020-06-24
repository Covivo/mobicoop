import debounce from 'lodash/debounce';
import React, { useRef, useEffect, useCallback } from 'react';
import Quill from 'quill';
import { FormHelperText, FormControl, InputLabel } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import styles from './richTextInputStyles';

const useStyles = makeStyles(styles, { name: 'RaRichTextInput' });

var toolbarOptions = [
    ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
    ['blockquote', 'code-block'],
  
    [{ 'header': 1 }, { 'header': 2 }],               // custom button values
    [{ 'list': 'ordered'}, { 'list': 'bullet' }],

    [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
  
    [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
  
    [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
    [{ 'font': [] }],
    [{ 'align': [] }],

    ['link'],
  
    ['clean']                                         // remove formatting button
];

  
const RichTextInput = ({
    options = {}, // Quill editor options
    record = {},
    isRequired = false,
    id,
    value,
    onChange,
    fullWidth = true,
    configureQuill,
    helperText = false,
    label,
    margin = 'dense',
    ...rest
}) => {
    const classes = useStyles();
    const quillInstance = useRef();
    const divRef = useRef();
    const editor = useRef();

    const lastValueChange = useRef(value);

    const touched = false;
    const error = "";

    const onTextChange = useCallback(
        debounce(() => {
            const value =
                editor.current.innerHTML === '<p><br></p>'
                    ? ''
                    : editor.current.innerHTML;
            lastValueChange.current = value;
            onChange(value);
        }, 500),
        []
    );

    useEffect(() => {
        quillInstance.current = new Quill(divRef.current, {
            modules: { toolbar : toolbarOptions, clipboard: { matchVisual: false } },
            theme: 'snow',
            placeholder: 'Ecrivez votre texte...',
            ...options,
        });

        if (configureQuill) {
            configureQuill(quillInstance.current);
        }

        quillInstance.current.setContents(
            quillInstance.current.clipboard.convert(value)
        );

        editor.current = divRef.current.querySelector('.ql-editor');
        quillInstance.current.on('text-change', onTextChange);

        return () => {
            quillInstance.current.off('text-change', onTextChange);
            onTextChange.cancel();
            quillInstance.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        if (lastValueChange.current !== value) {
            const selection = quillInstance.current.getSelection();
            quillInstance.current.setContents(
                quillInstance.current.clipboard.convert(value)
            );
            if (selection && quillInstance.current.hasFocus()) {
                quillInstance.current.setSelection(selection);
            }
        }
    }, [value]);

    return (
        <FormControl
            error={!!(touched && error)}
            fullWidth={fullWidth}
            className="ra-rich-text-input"
            margin={margin}
        >
            {label !== '' && label !== false && (
                <InputLabel shrink htmlFor={id} className={classes.label} required={isRequired}>
                    { label }
                </InputLabel>
            )}
            <div data-testid="quill" ref={divRef} style={{minHeight:'200px'}} />
            {helperText || (touched && error) ? (
                <FormHelperText
                    error={!!error}
                    className={!!error ? 'ra-rich-text-input-error' : ''}
                >
                    {error}
                </FormHelperText>
            ) : null}
        </FormControl>
    );
};



export default RichTextInput;