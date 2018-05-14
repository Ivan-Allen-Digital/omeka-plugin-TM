import argparse
import json

import requests

def replace_tag_for_document(id, api_key, url, currentTag, replacementTag):
    eqs = '===================================================================='
    print(eqs)
    print('Document', id)
    print(eqs)
    url = url.rstrip('/') + '/api/'
    req = requests.get(url + 'items/' + str(id))
    item = req.json()
    if 'message' in item and item['message'].endswith('Record not found.'):
        print('Record with id ' + str(id) + ' could not be found.')
        return

    if 'tags' in item:
        updatedTags = []
        for tag in item['tags']:
            originalTagName = tag['name']
            updatedTag = tag
            if tag['name'] == currentTag:
                updatedTag['name'] = replacementTag
                del updatedTag['id']
                del updatedTag['url']
            if originalTagName != currentTag or replacementTag != '':
                updatedTags.append(updatedTag)
        item['tags'] = updatedTags

    print('tags:', json.dumps(item['tags'], sort_keys=True, indent=4))
    req = requests.put(url + 'items/' + str(id) + '?key=' + api_key, json=item)
    print('update request sent')

if __name__ == "__main__":
    parser = argparse.ArgumentParser(
        description='Map a tag to another tag for a set of Omeka items.'
    )
    parser.add_argument('url', help='Base URL for the Omeka instance')
    parser.add_argument('key', help='API key for the Omeka instance')
    parser.add_argument('currentTag', help='Name of tag to replace')
    parser.add_argument('-r', '--replacementTag',
                        help='Name of replacement tag',
                        type=str,
                        default='')
    parser.add_argument('-s', '--start',
                        help='Document ID where tagging should begin',
                        type=int, default=0)
    parser.add_argument('-e', '--end',
                        help='Document ID where tagging should end',
                        type=int, default=100000)

    args = parser.parse_args()
    api_key = args.key
    currentTag = args.currentTag
    replacementTag = args.replacementTag

    for i in range(args.start, args.end):
        replace_tag_for_document(i, args.key, args.url, currentTag, replacementTag)
