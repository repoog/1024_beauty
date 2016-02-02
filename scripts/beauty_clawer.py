# coding: UTF-8
import requests
import re
import os
import sys

class HuabanCrawler():
    def __init__(self):
        self.homeUrl = "http://huaban.com/favorite/beauty/"
        self.images = []
        self.inter = "http://127.0.0.1/in_media_db.php"
        self.imgPath = "../www/weixin/images";
        if not os.path.exists(self.imgPath):
            os.mkdir(self.imgPath)

    def __load_homePage(self):
        return requests.get(url = self.homeUrl).content

    def __make_ajax_url(self, No):
        return self.homeUrl + "?i5p998kw&max=" + No + "&limit=20&wfl=1"

    def __load_more(self, maxNo):
        return requests.get(url = self.__make_ajax_url(maxNo)).content

    def __process_data(self, htmlPage):
        prog = re.compile(r'app\.page\["pins"\].*')
        appPins = prog.findall(htmlPage)
        # 将js中的null定义为Python中的None
        null = None
        true = True
        if appPins == []:
            return None
        result = eval(appPins[0][19:-1])

        for i in result:
            if ('image' == i["file"]["type"][:5]) and ('jpeg' == i["file"]["type"][6:]):
                info = {}
                info['id'] = str(i['pin_id'])
                info['url'] = "http://img.hb.aicdn.com/" + i["file"]["key"] + "_fw658"
                info['type'] = 'jpeg'
            else:
                continue
            self.images.append(info)

    def __save_image(self, imageName, content):
        with open(imageName, 'wb') as fp:
            fp.write(content)

    def get_image_info(self, num=20):
        self.__process_data(self.__load_homePage())
        for i in range((num-1)/20):
            self.__process_data(self.__load_more(self.images[-1]['id']))
        return self.images

    def down_images(self):
        os.system("/bin/bash file_clean.sh -C")
        print "{} image will be download".format(len(self.images))
        for key, image in enumerate(self.images):
            print 'download {0} ...'.format(key)
            try:
                req = requests.get(image["url"])
            except :
                print 'error'
            imageName = os.path.join(self.imgPath, image["id"] + "." + image["type"])
            self.__save_image(imageName, req.content)

    def in_media_db(self):
        """ call php interface to upload images """
        os.system("/bin/bash file_clean.sh -H")
        requests.get(url = self.inter)

if __name__ == '__main__':
    hc = HuabanCrawler()
    hc.get_image_info((int)(sys.argv[1]))
    hc.down_images()
    hc.in_media_db()
